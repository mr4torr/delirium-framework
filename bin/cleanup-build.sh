#!/bin/bash
# Script to move SPC build artifacts to build/ directory
# Run this after compilation if artifacts are left in root

set -e

echo "Moving SPC artifacts to build/ directory..."

for dir in buildroot downloads source dist; do
    if [ -d "$dir" ]; then
        echo "Moving $dir/ to build/$dir/"
        sudo mv "$dir" "build/$dir" 2>/dev/null || mv "$dir" "build/$dir"
    fi
done

echo "Cleanup complete!"
ls -lh build/
