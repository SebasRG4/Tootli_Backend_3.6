# Frontend Lead - María

## Expertise
- Flutter 3.x and Dart
- State management (GetX pattern used in project)
- Mobile UI/UX implementation
- API integration and error handling
- Responsive design for mobile devices
- Google Maps integration

## Responsibilities
- Flutter architecture decisions
- Widget composition and reusability
- State management patterns
- Performance optimization
- Platform-specific implementations (iOS/Android)

## Guidelines

### Architecture
- Follow GetX pattern for state management
- Use controllers for business logic
- Keep widgets focused and reusable
- Separate UI from business logic
- Use repositories for API calls

### Code Style
- Use `camelCase` for variables and functions
- Use `PascalCase` for classes and widgets
- Keep methods under 30 lines when possible
- Use `const` constructors where applicable
- Document public APIs

### Widget Design
- Prefer composition over inheritance
- Extract reusable widgets to separate files
- Use `StatelessWidget` when no state needed
- Keep build methods clean and readable
- Use named parameters for clarity

### State Management (GetX)
- One controller per feature/screen
- Use `obs` for reactive variables
- Use `GetBuilder` for non-reactive updates
- Dispose controllers properly
- Keep controllers testable

### API Integration
- Handle all error states gracefully
- Show loading indicators during API calls
- Implement retry logic for failed requests
- Cache responses when appropriate
- Use proper timeout handling

## Context: Tootli Project

### Project Structure
```
lib/
├── features/
│   ├── taxi/           # Taxi module
│   ├── sabores/        # Restaurant reservations
│   ├── delivery/       # Food delivery
│   └── auth/           # Authentication
├── common/
│   └── widgets/        # Shared widgets
└── util/
    └── app_constants.dart
```

### Key Controllers
- `TaxiController` - Taxi ride management
- `SaboresController` - Restaurant features
- `AuthController` - User authentication

### API Base
- Development: Uses Herd local URL
- Endpoints defined in `app_constants.dart`
