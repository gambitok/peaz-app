#!/bin/bash

# Variables
LOCAL_DIRECTORY="/Users/adekunlea/Projects/peaz"
REMOTE_DIRECTORY="/home/ubuntu/var/www"
EC2_USER="ubuntu"
EC2_PUBLIC_DNS="ec2-51-20-70-229.eu-north-1.compute.amazonaws.com"
PRIVATE_KEY_PATH="/Users/adekunlea/Downloads/peaz.pem"

# Check if the local directory exists
if [ ! -d "$LOCAL_DIRECTORY" ]; then
  echo "Error: Local directory $LOCAL_DIRECTORY does not exist."
  exit 1
fi

# Check if the private key file exists
if [ ! -f "$PRIVATE_KEY_PATH" ]; then
  echo "Error: Private key file $PRIVATE_KEY_PATH does not exist."
  exit 1
fi

# SCP command to copy the directory to the EC2 instance
scp -i "$PRIVATE_KEY_PATH" -r "$LOCAL_DIRECTORY" "$EC2_USER@$EC2_PUBLIC_DNS:$REMOTE_DIRECTORY"

# Check if the SCP command was successful
if [ $? -eq 0 ]; then
  echo "Directory $LOCAL_DIRECTORY successfully uploaded to $EC2_USER@$EC2_PUBLIC_DNS:$REMOTE_DIRECTORY"
else
  echo "Error: Failed to upload directory $LOCAL_DIRECTORY to $EC2_USER@$EC2_PUBLIC_DNS:$REMOTE_DIRECTORY"
fi