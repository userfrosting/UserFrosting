#!/bin/bash
docker run -d -i -t -p 80:80 -p 3306:3306 -v ${PWD}:/app lamp
