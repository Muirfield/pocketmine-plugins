#!/bin/sh
#
# Dig through PocketMine sources and find event classes
#
fatal() {
  echo "$@" 1>&2
  exit 1
}
[ $# -ne 1 ] && fatal "Must specify a PocketMine-MP source directory"
[ ! -d "$1" ] && fatal "$1: Is not a directory"
eventdir=$(find "$1" -type d -name "event")
[ -z "$eventdir" ] && fatal "$1: does not contain PocketMine-MP source code"

find $eventdir -name '*.php' | (
  while read fp
  do
    event=$(grep '^class '<$fp | grep ' extends ' | grep 'Event' |\
        sed \
          -e 's/^class\s*//' \
          -e 's/{$//' \
          -e 's/\s*implements\s*Cancellable\s*//' \
          -e 's/\s*extends\s*/ /')
    [ -z "$event" ] && continue
    grep -q '@deprecated' $fp && continue
    if grep -q 'handlerList' $fp ; then
      handler=yes
    else
      handler=no
    fi
    echo $(basename $(dirname $fp)) $event $handler
  done
)
