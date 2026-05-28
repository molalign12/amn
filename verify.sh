#!/bin/bash
# AMNEN Hotel - Installation Verification Script
# Run this after extracting files to XAMPP

echo "================================================"
echo "AMNEN Hotel - System Verification"
echo "================================================"
echo ""

# Color codes
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

checks_passed=0
checks_failed=0

# Check 1: XAMPP installation
echo -n "Checking XAMPP location... "
if [ -d "C:/xampp/htdocs" ] || [ -d "/Applications/XAMPP/htdocs" ] || [ -d "/opt/xampp/htdocs" ]; then
    echo -e "${GREEN}✓${NC}"
    ((checks_passed++))
else
    echo -e "${RED}✗${NC}"
    ((checks_failed++))
fi

# Check 2: Files exist
echo -n "Checking core files... "
if [ -f "setup.php" ] && [ -f "bootstrap.php" ] && [ -f "index.php" ]; then
    echo -e "${GREEN}✓${NC}"
    ((checks_passed++))
else
    echo -e "${RED}✗${NC}"
    ((checks_failed++))
fi

# Check 3: Config directory
echo -n "Checking config directory... "
if [ -d "config" ] && [ -f "config/config.php" ] && [ -f "config/db.php" ]; then
    echo -e "${GREEN}✓${NC}"
    ((checks_passed++))
else
    echo -e "${RED}✗${NC}"
    ((checks_failed++))
fi

# Check 4: Classes directory
echo -n "Checking classes directory... "
if [ -d "classes" ] && [ -f "classes/User.php" ] && [ -f "classes/Room.php" ]; then
    echo -e "${GREEN}✓${NC}"
    ((checks_passed++))
else
    echo -e "${RED}✗${NC}"
    ((checks_failed++))
fi

# Check 5: SQL schema
echo -n "Checking database schema... "
if [ -f "sql/schema.sql" ]; then
    echo -e "${GREEN}✓${NC}"
    ((checks_passed++))
else
    echo -e "${RED}✗${NC}"
    ((checks_failed++))
fi

# Check 6: .env file
echo -n "Checking environment file... "
if [ -f ".env" ]; then
    echo -e "${GREEN}✓${NC}"
    ((checks_passed++))
else
    echo -e "${RED}✗${NC}"
    ((checks_failed++))
fi

echo ""
echo "================================================"
echo "Results: ${GREEN}$checks_passed passed${NC}, ${RED}$checks_failed failed${NC}"
echo "================================================"
echo ""

if [ $checks_failed -eq 0 ]; then
    echo -e "${GREEN}✓ All checks passed!${NC}"
    echo ""
    echo "Next steps:"
    echo "1. Start Apache and MySQL in XAMPP"
    echo "2. Go to: http://localhost/amnen/setup.php"
    echo "3. Wait for green checkmarks"
    echo "4. Go to: http://localhost/amnen/index.php"
    echo "5. Login with: admin / Admin@123"
    echo ""
else
    echo -e "${RED}✗ Some checks failed${NC}"
    echo "Please verify the installation:"
    echo "1. Extract files to: C:\xampp\htdocs\amnen\"
    echo "2. All folders must be present"
    echo "3. Check QUICK_START.txt for help"
fi

echo ""
