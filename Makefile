help:
	echo "Cat this file..."
dev:
	docker compose -f docker-compose.dev.yaml up
dev-d:
	docker compose -f docker-compose.dev.yaml up -d
down:
	docker compose down
