#!/bin/bash

for i in {1..12}
do
  curl -s "https://haier.shwdsg.com/api/lottery/batch_send_red_packet_task?key=cron_secure_key_123&limit=120" > /dev/null
  sleep 5
done
