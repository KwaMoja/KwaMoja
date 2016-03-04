#!/bin/bash

ROOT_DIR=$PWD
cd $ROOT_DIR
for f in `find . -name "*.php" -o -name "*.inc"`
do
    newname=`echo $f | cut -c3-`
    FileName="$ROOT_DIR/$newname"
#    echo $FileName
    output=$((php -l $FileName ) 2>&1)

    if [ $? != 0 ]
    then
		echo '**Error** '$output
		echo ''
    fi
done
