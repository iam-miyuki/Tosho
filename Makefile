help:
	echo "Cat this file..."
dev:
	docker compose -f docker-compose.yaml up
dev-d:
	docker compose -f docker-compose.yaml up -d
down:
	docker compose -f docker-compose.yaml down
