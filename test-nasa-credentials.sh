#!/bin/bash

echo "🔐 Testing NASA Earthdata Credentials"
echo "======================================"
echo ""

# Check if credentials are in .env
if [ -f .env ]; then
    USERNAME=$(grep NASA_EARTHDATA_USERNAME .env | cut -d '=' -f2)
    PASSWORD=$(grep NASA_EARTHDATA_PASSWORD .env | cut -d '=' -f2)
    
    if [ -z "$USERNAME" ] || [ -z "$PASSWORD" ]; then
        echo "❌ Credentials not found in .env file"
        echo "   Add NASA_EARTHDATA_USERNAME and NASA_EARTHDATA_PASSWORD"
        exit 1
    fi
    
    echo "📝 Username: $USERNAME"
    echo "🔑 Password: ${PASSWORD:0:4}****"
    echo ""
    echo "🌐 Testing authentication..."
    echo ""
    
    # Test authentication
    RESPONSE=$(curl -s -w "\n%{http_code}" -X POST \
        "https://appeears.earthdatacloud.nasa.gov/api/login" \
        -u "$USERNAME:$PASSWORD" \
        -H "Content-Type: application/json")
    
    # Extract HTTP code
    HTTP_CODE=$(echo "$RESPONSE" | tail -n1)
    BODY=$(echo "$RESPONSE" | head -n-1)
    
    echo "HTTP Status: $HTTP_CODE"
    echo ""
    
    if [ "$HTTP_CODE" -eq 200 ]; then
        echo "✅ SUCCESS! Credentials are valid"
        echo ""
        echo "Response:"
        echo "$BODY" | python3 -m json.tool 2>/dev/null || echo "$BODY"
        echo ""
        echo "🚀 You can use NASA_EARTHDATA_MOCK=false in production"
        exit 0
    else
        echo "❌ FAILED! Credentials are invalid"
        echo ""
        echo "Response:"
        echo "$BODY"
        echo ""
        echo "📋 Next steps:"
        echo "   1. Register at: https://urs.earthdata.nasa.gov/users/new"
        echo "   2. Login at: https://appeears.earthdatacloud.nasa.gov/"
        echo "   3. Update credentials in .env"
        echo ""
        echo "⚠️  For now, use NASA_EARTHDATA_MOCK=true"
        exit 1
    fi
else
    echo "❌ .env file not found"
    exit 1
fi
