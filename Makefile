help:
	echo "Cat this file..."
dev:
	docker compose -f docker-compose.dev.yaml up --build
dev-d:
	docker compose -f docker-compose.dev.yaml up -d
down:
	docker stop tosho-app-1 tosho-database-1 tosho-mailer-1 
