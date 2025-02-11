echo Building Docker Image...
docker build -t laravel-app .

if %ERRORLEVEL% NEQ 0 (
    echo Docker build failed!
    exit /b %ERRORLEVEL%
)

echo Running Docker Container...
docker run -d -p 80:80 --name laravel-container laravel-app

echo Laravel container is running on http://localhost
