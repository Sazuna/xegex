#!/bin/bash

mysql -u $1 -p xegex < tables.sql
mysql -u $1 -p xegex < populate.sql
