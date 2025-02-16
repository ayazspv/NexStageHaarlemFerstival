#!/usr/bin/env bash

echo "[+] Building Docker Image"
docker build -t haarlemfestival .

if [ $? -ne 0 ]; then
    echo "[-] Docker build failed!"
    exit 1
fi

echo "[+] Starting Docker Container"
docker run -d -p 80:80 --name haarlemfestival haarlemfestival

if [ $? -ne 0 ]; then
    echo "[-] Docker run failed!"
    exit 1
fi

echo "\n[!] Laravel container is running on http://localhost"
