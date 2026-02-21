.PHONY: build run dev clean

BIN=./bin/tootli-worker

build:
	go build -o $(BIN) .

run: build
	$(BIN)

dev:
	go run .

clean:
	rm -f $(BIN)

test:
	go test ./...
