#!/bin/sh
main() {
  mode="text"
  case "$1" in
      -t|--text)
	  mode="text"
	  shift
	  ;;
      -h|--html)
	  mode="html"
	  shift;
	  ;;
      -b|--browser|--viewhtml)
	  mode="viewhtml"
	  shift
	  ;;
  esac

  src="$1"
  [ -f "$src" ] || fatal "$src: not found"

  exec 3<&0-
  exec <"$src"
  # Get the Pocket Plugin meta data
  while read LN
  do
    if [ x"$LN" = x"__PocketMine Plugin__" ] ; then
      # OK, found meta data block
      while read LN
      do
	[ x"$LN" = x"" ] && continue
	if grep -q '=' <<<"$LN" ; then
	  k=$(cut -d'=' -f1 <<<"$LN")
	  v=$(cut -d'=' -f2- <<<"$LN")
	  eval meta_${k}=\"\$v\"
	else
	  break 2
	fi
      done
    fi
  done
  exec <&3-

  SUBS=()
  for k in name description version author class apiversion
  do
    eval v=\"\$meta_${k}\"
    [ x"$v" = x"" ] && fatal "$k keyword missing"

    SUBS+=( -e 's/META_'"$(tr a-z A-Z <<<"$k")"'/'"$(tr -d / <<<"$v")"'/' )
  done

  grep '^[ 	]*\*\* *' "$src" | sed \
      -e 's/^[ 	]*\*\*[ 	]*$//' \
      -e 's/^[ 	]*\*\* //' \
      -e 's/^[ 	]*\*\*	/	/' \
      -e 's/^[ 	]*\*\*\/.*//' "${SUBS[@]}" | (
      if ! type markdown >/dev/null 2>&1 ; then
	mode=text
      fi

      case "$mode" in
	text)
	  ## - `text` : plain text output
	  cat
	  ;;
	html)
	  ## - `html` : HTML document
	  gen_html
	  ;;
	viewhtml)
	  ## - `viewhtml` : Will show manual on a browser window.
	  if type firefox ; then
	    # If firefox is available, we start it with a new
	    # profile. This make sure that we do not reuse any
	    # running firefox instance, and a new instance is
	    # created.  When the users closes the firefox window
	    # then we know that we can delete the temp file.
	    wrkdir=$(mktemp -d)
	    trap "rm -rf $wrkdir" EXIT
	    gen_html > $wrkdir/assist_doc.html
	    HOME=$wrkdir firefox -no-remote $wrkdir/assist_doc.html
	    rm -rf $wrkdir
	  else
	    local output=/tmp/md.$UID.html
	    rm -f $output
	    gen_html > $output
	    xdg-open $output
	  fi
	  ;;
	*)
	  fatal "Invalid option"
	  ;;
      esac
  )
}


######################################################################
#
# The following are simple, useful support functions
#
######################################################################
fatal() {
  echo "$@" 1>&2
  exit 1
}

gen_html() {
    #
    # Genreate HTML using Markdown and adding Javascript to generate
    # table of contents automatically
    #
    cat <<-EOF
	<script>
	window.onload = function () {
	    var toc = "";
	    var level = 0;

	    document.getElementById("contents").innerHTML =
	    	document.getElementById("contents").innerHTML.replace(
	    		/<h([\d])>([^<]+)<\/h([\d])>/gi,
	    		function (str, openLevel, titleText, closeLevel) {
	    			if (openLevel != closeLevel) {
	    				return str;
	    			}

	    			if (openLevel > level) {
	    				toc += (new Array(openLevel - level + 1)).join("<ul>");
	    			} else if (openLevel < level) {
	    				toc += (new Array(level - openLevel + 1)).join("</ul>");
	    			}

	    			level = parseInt(openLevel);

	    			var anchor = titleText.replace(/ /g, "_");
	    			toc += "<li><a href=\"#" + anchor + "\">" + titleText
	    				+ "</a></li>";

	    			return "<h" + openLevel + "><a name=\"" + anchor + "\">"
	    				+ titleText + "</a></h" + closeLevel + ">";
	    		}
	    	);

	    if (level) {
	    	toc += (new Array(level + 1)).join("</ul>");
	    }

	    document.getElementById("toc").innerHTML += toc;
	};
	</script>
	<body>
	<h1>$meta_name</h1>
	<div id="toc">
	  <h3>Table of Contents</h3>
	</div>
	<hr/>
	<div id="contents">
	EOF
	markdown
	cat <<-EOF
	</dvi>
	</body>
	EOF
}

main "$@"

