CodeCommunity
======================

[![Build Status](https://travis-ci.com/iFaxity/ramverk1-projekt.svg?branch=master)](https://travis-ci.com/iFaxity/ramverk1-projekt)
[![Build Status](https://scrutinizer-ci.com/g/iFaxity/ramverk1-projekt/badges/build.png?b=master)](https://scrutinizer-ci.com/g/iFaxity/ramverk1-projekt/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/iFaxity/ramverk1-projekt/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/iFaxity/ramverk1-projekt/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/iFaxity/ramverk1-projekt/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/iFaxity/ramverk1-projekt/?branch=master)

A stackoverflow inspired website made using the [Anax](https://github.com/canax) framework.

## Installation

To install the project you need to clone this github repository and install the packages. Assuming you already have make, composer, apache and php (with most common plugins) installed and configured properly, or docker-compose to make this step easier.

```
# Clone project
git clone git@github.com:iFaxity/ramverk1-projekt.git

# Then you need to initialize the database
chmod +x sql/setup.sh && sql/setup.sh

# Then you can either run the server using docker-compose:
docker-compose up -d server

# Or install it manually with Apache by moving the folder to your webroot, then install the packages with make.
make install
```
