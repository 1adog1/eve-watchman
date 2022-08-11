#!/bin/bash

/opt/elasticbeanstalk/bin/get-config environment | jq -r 'to_entries | .[] | "export \(.key)=\"\(.value)\""' > /opt/elasticbeanstalk/deployment/env-vars
