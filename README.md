# Application Test

## Prerequisites

* A machine with Docker and Docker Compose installed.

## Getting Started

1. Clone/fork the repository.
1. Navigate to the root directory of the project (e.g., `cd /path/to/application-test`).
1. Run the application: `docker compose -p application-test up -d` 
1. Install Composer dependencies: `docker exec -it application-test-backend-1 composer install`
1. Clear the cache: `docker exec -it application-test-backend-1 php bin/console cache:clear`
1. Bootstrap the database: `docker exec -it application-test-backend-1 php bin/console app:db:bootstrap-dev`(this operation will also populate the database with some dummy data for testing).
1. Verify that the backend application is running by visiting `http://localhost:3050` in your browser.
1. Verify that the frontend application is running by visiting `http://localhost:3051` in your browser.

> NOTE: For both applications (backend and frontend), you can override the default environment variables by creating a .env.local file in the respective root directory. 
This is useful, for example, to change the port on which the applications are running or to change the URI of the GraphQL endpoint used by the frontend application. Restart the application after making changes to the `.env.local` file.

> NOTE: A web-based GraphQL client is available at `http://localhost:3050/graphiql` for you to interact with the API.

## Backend Development

### Exercise 1: GraphQL Resolver Methods

Implement GraphQL resolver methods for the `\App\Document\Event::participants` and `\App\Document\Event::program` attributes.

1. Open the `backend/src/GraphQL/Resolver/EventResolver.php` file.
1. Implement the `resolveParticipants` and `resolveProgram` methods as per the instructions in the comments.

### Exercise 2: Custom Validator

Implement a custom validator for the `Event::program` collection.

1. Open the `backend/src/Validator/ConstraintValidProgramValidator.php` file.
1. Implement the `program` validation logic as per the instructions in the comments.


## Frontend Development

### Exercise 1: Query State and Outcome Handling

Implement GraphQL query state and outcome handling.

1. Open the `frontend/src/app/_components/Schedule/Schedule.tsx` file.
1. Implement loading and error handling as described in the component's comments.

### Exercise 2: Component Implementation

Implement the `Program` component.

1. Open the `frontend/src/app/events/[id]/_components/Program/Program.tsx` file.
1. Implement the component as described in the comments.

