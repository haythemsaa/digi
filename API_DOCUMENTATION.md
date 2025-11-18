# DigiParc Fleet Management - API Documentation

## Base URL
```
http://localhost/digi/api
```

## Authentication

All API endpoints (except `/login`) require authentication using a Bearer token.

### Get API Token

**Endpoint:** `POST /api/login`

**Request:**
```json
{
  "email": "admin@digiparc.local",
  "password": "admin123"
}
```

**Response:**
```json
{
  "success": true,
  "token": "a1b2c3d4e5f6...",
  "user": {
    "id": 1,
    "username": "admin",
    "email": "admin@digiparc.local",
    "first_name": "Admin",
    "last_name": "DigiParc",
    "role": "admin"
  }
}
```

### Using the Token

Include the token in the `Authorization` header for all subsequent requests:

```
Authorization: Bearer a1b2c3d4e5f6...
```

---

## API Endpoints

### 1. General

#### Get API Info
```
GET /api
```

Returns API version and available endpoints.

**Response:**
```json
{
  "name": "DigiParc Fleet Management API",
  "version": "1.0.0",
  "endpoints": { ... }
}
```

---

### 2. Vehicles

#### Get All Vehicles
```
GET /api/vehicles
```

**Response:**
```json
{
  "success": true,
  "vehicles": [
    {
      "id": 1,
      "registration_number": "ABC-123",
      "brand": "Mercedes",
      "model": "Sprinter",
      "type": "van",
      "fuel_type": "diesel",
      "year": 2022,
      "odometer": 45000,
      "status": "active"
    }
  ]
}
```

#### Get Vehicle by ID
```
GET /api/vehicle/{id}
```

**Response:**
```json
{
  "success": true,
  "vehicle": {
    "id": 1,
    "registration_number": "ABC-123",
    "brand": "Mercedes",
    "model": "Sprinter",
    ...
  }
}
```

---

### 3. GPS Tracking

#### Get Latest Positions
```
GET /api/positions
```

**Response:**
```json
{
  "success": true,
  "positions": [
    {
      "id": 1,
      "registration_number": "ABC-123",
      "latitude": 36.8065,
      "longitude": 10.1815,
      "speed": 65.5,
      "heading": 180.0,
      "fuel_level": 75.5,
      "engine_status": true,
      "timestamp": "2024-01-15 14:30:00"
    }
  ]
}
```

#### Add GPS Position
```
POST /api/position
```

**Request:**
```json
{
  "vehicle_id": 1,
  "latitude": 36.8065,
  "longitude": 10.1815,
  "altitude": 10.5,
  "speed": 65.5,
  "heading": 180.0,
  "accuracy": 5.0,
  "satellites": 12,
  "odometer": 45123,
  "fuel_level": 75.5,
  "engine_status": true,
  "timestamp": "2024-01-15 14:30:00"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Position added successfully"
}
```

---

### 4. Transport Orders

#### Get All Orders
```
GET /api/orders
```

**Response:**
```json
{
  "success": true,
  "orders": [
    {
      "id": 1,
      "order_number": "TO-2024-0001",
      "client_id": 5,
      "company_name": "ABC Transport",
      "pickup_city": "Tunis",
      "delivery_city": "Sfax",
      "status": "in_transit",
      "total_amount": 450.00,
      "pickup_date": "2024-01-15 08:00:00"
    }
  ]
}
```

#### Get Order by ID
```
GET /api/order/{id}
```

**Response:**
```json
{
  "success": true,
  "order": {
    "id": 1,
    "order_number": "TO-2024-0001",
    "pickup_address": "123 Main St, Tunis",
    "delivery_address": "456 Second Ave, Sfax",
    ...
  }
}
```

#### Update Order Status
```
PUT /api/order/{id}/status
```

**Request:**
```json
{
  "status": "delivered"
}
```

**Allowed statuses:**
- `pending`
- `confirmed`
- `assigned`
- `in_transit`
- `delivered`
- `cancelled`

**Response:**
```json
{
  "success": true,
  "message": "Order status updated"
}
```

---

### 5. Maintenance

#### Get All Work Orders
```
GET /api/maintenance/work-orders
```

**Response:**
```json
{
  "success": true,
  "work_orders": [
    {
      "id": 1,
      "reference": "WO-2024-0001",
      "registration_number": "ABC-123",
      "type": "preventive",
      "status": "pending",
      "priority": "medium",
      "total_cost": 250.00
    }
  ]
}
```

#### Get Due Maintenances
```
GET /api/maintenance/due
```

Returns maintenance schedules that are due soon (within 7 days or 500 km).

**Response:**
```json
{
  "success": true,
  "due_maintenances": [
    {
      "vehicle_id": 1,
      "registration_number": "ABC-123",
      "maintenance_type": "Oil Change",
      "next_service_date": "2024-01-20",
      "next_service_km": 50000,
      "current_odometer": 49600
    }
  ]
}
```

---

### 6. Alerts

#### Get All Alerts
```
GET /api/alerts
```

**Response:**
```json
{
  "success": true,
  "speed_alerts": [
    {
      "id": 1,
      "vehicle_id": 1,
      "registration_number": "ABC-123",
      "speed": 135.5,
      "speed_limit": 120.0,
      "latitude": 36.8065,
      "longitude": 10.1815,
      "timestamp": "2024-01-15 14:30:00"
    }
  ],
  "geofence_alerts": [
    {
      "id": 1,
      "vehicle_id": 1,
      "registration_number": "ABC-123",
      "geofence_name": "Restricted Zone",
      "alert_type": "entry",
      "timestamp": "2024-01-15 12:00:00"
    }
  ]
}
```

---

### 7. Dashboard Statistics

#### Get Stats
```
GET /api/stats
```

**Response:**
```json
{
  "success": true,
  "stats": {
    "total_vehicles": 25,
    "active_vehicles": 20,
    "maintenance_vehicles": 3,
    "total_users": 45
  }
}
```

---

## Error Responses

All errors follow this format:

```json
{
  "error": "Error message description"
}
```

### HTTP Status Codes

- `200` - Success
- `400` - Bad Request (missing parameters)
- `401` - Unauthorized (invalid or missing token)
- `403` - Forbidden (insufficient permissions)
- `404` - Not Found
- `405` - Method Not Allowed
- `500` - Internal Server Error

---

## Usage Examples

### cURL Examples

**Login:**
```bash
curl -X POST http://localhost/digi/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@digiparc.local","password":"admin123"}'
```

**Get Vehicles (with token):**
```bash
curl -X GET http://localhost/digi/api/vehicles \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

**Add GPS Position:**
```bash
curl -X POST http://localhost/digi/api/position \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "vehicle_id": 1,
    "latitude": 36.8065,
    "longitude": 10.1815,
    "speed": 65.5,
    "fuel_level": 75.5,
    "engine_status": true
  }'
```

### JavaScript/Fetch Example

```javascript
// Login
const response = await fetch('http://localhost/digi/api/login', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    email: 'admin@digiparc.local',
    password: 'admin123'
  })
});

const data = await response.json();
const token = data.token;

// Get vehicles
const vehiclesResponse = await fetch('http://localhost/digi/api/vehicles', {
  headers: {
    'Authorization': `Bearer ${token}`
  }
});

const vehicles = await vehiclesResponse.json();
console.log(vehicles);
```

### Mobile App Integration

For mobile app development (React Native, Flutter, etc.):

1. **Authentication:** Store the token securely (AsyncStorage, SecureStorage)
2. **GPS Tracking:** Send position updates every 30-60 seconds
3. **Offline Support:** Queue GPS positions locally and sync when online
4. **Push Notifications:** Use the service worker for notifications

---

## Rate Limiting

Currently, there is no rate limiting implemented. For production:
- Recommended: 100 requests per minute per token
- GPS position updates: Max 1 per second per vehicle

---

## CORS

CORS is enabled for all origins (`Access-Control-Allow-Origin: *`).

For production, configure specific allowed origins in the API controller.

---

## Security Recommendations

1. **HTTPS:** Always use HTTPS in production
2. **Token Expiry:** Implement token expiration (currently tokens don't expire)
3. **JWT:** Consider using JWT instead of simple tokens
4. **IP Whitelisting:** Restrict API access to known IPs
5. **Rate Limiting:** Implement rate limiting to prevent abuse
6. **Input Validation:** All inputs are sanitized, but additional validation is recommended
7. **API Keys:** Consider requiring API keys for third-party integrations

---

## Support

For API support or questions:
- Email: support@digiparc.local
- Documentation: See README.md for full system documentation

---

**Version:** 1.0.0
**Last Updated:** 2024
