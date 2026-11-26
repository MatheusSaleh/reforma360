# Imagem das calculadoras Java da Receita (regime regular e Simples Nacional).
# O contexto de build é o repositório upstream em services/<repo>, usado sem modificações.
# O banco SQLite não entra na imagem: é gerado pelo serviço calculadora-db e montado em /app/calculadora/db.

# O upstream não versiona o maven-wrapper.jar, então usamos o Maven da imagem oficial.
FROM maven:3.9-eclipse-temurin-25 AS builder

WORKDIR /src

COPY . .

RUN --mount=type=cache,target=/root/.m2 \
    mvn -B -q -DskipTests package \
    && find target -maxdepth 1 -name '*.jar' ! -name '*-plain.jar' -exec cp {} /app.jar \;

FROM eclipse-temurin:25-jre-alpine

WORKDIR /app

COPY --from=builder /app.jar /app/app.jar

ENV TZ=America/Sao_Paulo \
    JAVA_OPTS="-XX:MaxRAMPercentage=75"

EXPOSE 8080

ENTRYPOINT ["sh", "-c", "exec java $JAVA_OPTS -Dspring.profiles.active=offline -jar /app/app.jar"]
