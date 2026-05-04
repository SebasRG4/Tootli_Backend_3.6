# Compilar
FROM golang:1.24-alpine AS builder

WORKDIR /app

# Instalar dependencias
COPY go.mod go.sum ./
RUN go mod download

# Copiar y compilar la app de Go
COPY . .
RUN cat models/order.go
RUN go build -o main .

# Imagen final ligera
FROM alpine:latest

# Instalar tzdata para soporte de zonas horarias
RUN apk add --no-cache tzdata

WORKDIR /app

# Obtener binario de la etapa anterior
COPY --from=builder /app/main .

EXPOSE 8080

CMD ["./main"]
