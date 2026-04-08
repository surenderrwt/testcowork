#!/usr/bin/env bash
# Double-click this file in Finder to run the full WooCommerce setup
cd /Users/surender-apple/Sites/testcowork
bash run-setup.sh 2>&1 | tee /tmp/tes2-setup.log
echo ""
echo "Setup log saved to /tmp/tes2-setup.log"
echo "Press any key to close..."
read -n 1
