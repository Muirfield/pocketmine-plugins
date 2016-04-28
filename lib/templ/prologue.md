<?php
if (!isset($v_forum_thread)) {
  echo "<!-- Add the line: -->\n";
  echo "<!-- php: \$v_forum_thread = \"http://forums.pocketmine.net/threads/XXXX\"; -->\n";
}
if (!isset($v_tag_url)) $v_tag_url="https://github.com/Muirfield/pocketmine-plugins/releases/tag/";
?>

<?php if (isset($v_forum_thread)) { ?>
**DO NOT POST QUESTIONS/BUG-REPORTS/REQUESTS IN THE REVIEWS**

It is difficult to carry a conversation in the reviews.  If you
have a question/bug-report/request please use the
[Thread](<?= $v_forum_thread?>) for
that.  You are more likely to get a response and help that way.
<?php } ?>

_NOTE:_

This documentation was last updated for version **<?=$yaml["version"]?>**.

<?php if (isset($yaml["website"])) {?>
Please go to
[github](<?=$yaml["website"]?>)
for the most up-to-date documentation.
<?php } ?>

You can also download this plugin from this [page](<?=$v_tag_url?><?=$yaml["name"].'-'.$yaml["version"]?>).
