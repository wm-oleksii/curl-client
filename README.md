# HTTP client on top of curl-impersonate

## Used pre-compiled binaries
https://github.com/lwthiker/curl-impersonate/releases/download/v0.5.4/curl-impersonate-v0.5.4.x86_64-linux-gnu.tar.gz

## Pre-compiled binaries 
Pre-compiled binaries for Linux and macOS (Intel) are available at the GitHub releases page.
Before you use them you need to install nss (Firefox's TLS library) and CA certificates:

* Ubuntu - sudo apt install libnss3 nss-plugin-pem ca-certificates
* Red Hat/Fedora/CentOS - yum install nss nss-pem ca-certificates
* Archlinux - pacman -S nss ca-certificates
* macOS - brew install nss ca-certificates


