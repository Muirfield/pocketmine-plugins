#!/bin/sh
cd $(dirname $0)
( cd libcommon ; phpdoc ../lib/phpdoctor.ini phpdoc.ini )
( cd GrabBag ; phpdoc ../lib/phpdoctor.ini phpdoc.ini )
markdown index.md
