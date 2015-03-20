#!/bin/sh
cd $(dirname $0) || exit 1
fatal() {
  echo "$@" 1>&2
  exit 1
}
if [ -x bin/php ] ; then
  PHP=bin/php
elif type php >/dev/null; then
  PHP="php --define phar.readonly=0"
  echo "Warning, using OS php, it may not work" 1>&2
else
  fatal "No valid PHP executable found"
fi
if [ -f ../mkplugin.php ] ; then
  MKPLUGIN="$PHP ../mkplugin.php"
else
  echo "Will not create plain plugin"
  MKPLUGIN=":"
fi
IMPORTMAP=../ImportMap
[ -d $IMPORTMAP ] || fatal "Unable to find Plugin source"

set -e
$PHP build.php -0		# Pass1: Create uncompressed version
$PHP build.php $IMPORTMAP	# Pass2: Create multi use version
$PHP build.php			# Pass3: Create cli version
$MKPLUGIN $IMPORTMAP		# Pass4: Create plain plugin
