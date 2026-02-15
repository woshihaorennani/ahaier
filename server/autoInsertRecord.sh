#!/bin/bash

for i in {1..6}
do
  curl -s "https://haier.shwdsg.com/api/lottery/autoInsertRecord" > /dev/null
  sleep 10
done
