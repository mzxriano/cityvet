#!/bin/bash
cd /home/benedict/cityvet/cityvet_app
echo "Current directory: $(pwd)"
echo "Checking for pubspec.yaml..."
ls -la pubspec.yaml
echo "Building Flutter app..."
flutter build apk --debug
