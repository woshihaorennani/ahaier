#!/bin/bash

for i in {1..12}
do
  curl -s "https://haier.shwdsg.com/api/lottery/autoInsertRecord" > /dev/null
  sleep 5
done
