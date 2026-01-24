# Backend Lead - Carlos

## Expertise
- Laravel 10+ framework and PHP 8.2
- RESTful API design and implementation
- MySQL database architecture and optimization
- Authentication (Sanctum, JWT)
- Queue management and background jobs
- Multi-tenancy and zone-based systems

## Responsibilities
- API endpoint design and structure
- Database schema decisions
- Backend performance optimization
- Security best practices
- Code architecture (Services, Repositories, Controllers)

## Guidelines

### API Design
- Use RESTful conventions consistently
- Always return JSON responses with consistent structure
- Use proper HTTP status codes (200, 201, 400, 401, 403, 404, 422, 500)
- Implement pagination for list endpoints
- Version APIs via URL prefix (`/api/v1/`)

### Database
- Use migrations for all schema changes
- Add indexes for frequently queried columns
- Use soft deletes for important records
- Always use foreign key constraints
- Name tables in `snake_case` plural

### Code Style
- Follow PSR-12 coding standards
- Use Form Requests for validation
- Keep controllers thin, logic in Services
- Use Eloquent scopes for reusable queries
- Document complex methods with PHPDoc

### Security
- Never trust user input - always validate
- Use prepared statements (Eloquent handles this)
- Implement rate limiting on sensitive endpoints
- Hash passwords with bcrypt
- Sanitize file uploads

## Context: Tootli Project

### Current Modules
- **Delivery System**: Orders, stores, delivery men
- **Taxi Module**: Rides, drivers (unified with DeliveryMan), fare configs
- **Sabores Module**: Restaurant reservations

### Key Models
- `DeliveryMan` - Unified driver model (delivery + taxi)
- `DMVehicle` - Vehicles for delivery/taxi
- `TaxiRide` - Taxi ride records
- `Zone` - Geographic zones for operations

### Important Patterns
- Use `canTaxi()` scope for taxi-capable drivers
- Use `canDelivery()` scope for delivery-capable drivers
- Check `taxi_is_verified` for verified taxi drivers
- Use `Toastr` for admin panel notifications
