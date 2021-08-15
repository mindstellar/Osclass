#!/bin/sh
# ------------------------------------------------------------------
## Author = Navjot Tomer
##
## Description
## Basic script for generating Osclass release zip package
##
##
# Current latest osclass tag
LATEST_TAG=$(git describe --tags --abbrev=0)

echo 'Osclass build started for v'"$LATEST_TAG"
# Delete any previous release directory
DIR="release"
if [ -d "$DIR" ]; then
  ### An existing directory exists delete it ###
  echo 'removing existing release directory'
  rm -r release
fi

###  Make directory ###
mkdir release

# Create Osclass release archive
git archive --output=release/osclass_no_theme.zip "$LATEST_TAG"
unzip -qq release/osclass_no_theme.zip -d release/osclass
# Download latest bender-theme release from repository
echo 'Downloading latest bender theme'
cd release && curl -s https://api.github.com/repos/mindstellar/theme-bender/releases/latest |
  grep 'browser_download_url' |
  head -1 |
  cut -d '"' -f 4 |
  wget -qi - && unzip -qq bender_*.zip -d osclass/oc-content/themes/
# create new release with bender included
(zip ./osclass_v"$LATEST_TAG".zip -r osclass 1>/dev/null)
echo 'Build create successfully in release/osclass_v'"$LATEST_TAG"'.zip'
