#!/bin/bash
# Updates the Debian repo for nightly builds
#
# To create repo:
# aptly repo create -distribution=nightly -component=main -architectures=amd64,i386,all yarn-nightly
# aptly publish repo -gpg-key=4F77679369475BAA -architectures=i386,amd64 yarn-nightly yarn-nightly

set -ex

# Check if there's any .deb files to process
files=$(shopt -s nullglob; echo ./nightly/deb-incoming/*.deb)
if (( ${#files} )); then
  aptly repo add -remove-files=true yarn-nightly ./nightly/deb-incoming/
  aptly publish update -gpg-key=4F77679369475BAA nightly yarn-nightly
else
  echo 'No packages to process'
fi
