$baseUrl = "http://127.0.0.1:8000/api"

Write-Host "========== ADMIN LOGIN TEST ==========" -ForegroundColor Cyan

# Test 1: Login
$loginData = @{
    email = "admin@gym.com"
    password = "password"
} | ConvertTo-Json

Write-Host "Sending login request..." -ForegroundColor Yellow
$loginResponse = Invoke-WebRequest -Uri "$baseUrl/login" `
    -Method POST `
    -Headers @{
        'Content-Type' = 'application/json'
        'Accept' = 'application/json'
    } `
    -Body $loginData `
    -UseBasicParsing

$loginBody = $loginResponse.Content | ConvertFrom-Json
Write-Host "Login status: $($loginResponse.StatusCode)" -ForegroundColor Green
Write-Host "Response:" -ForegroundColor Yellow
$loginBody | ConvertTo-Json | Write-Host

# The token is inside the data object
$token = $loginBody.data.token
if ($token) {
    Write-Host "[OK] Token obtained: $($token.Substring(0, 20))..." -ForegroundColor Green
    
    # Test 2: Create Trainer
    Write-Host "`n========== TRAINER CREATION TEST ==========" -ForegroundColor Cyan
    
    $trainerData = @{
        first_name = "Test"
        last_name = "Trainer"
        email = "testtrainer$(Get-Random)@gym.test"
        specialization = "Testing"
        phone = "555-TEST"
        hourly_rate = 75
    } | ConvertTo-Json
    
    Write-Host "Sending trainer creation request..." -ForegroundColor Yellow
    Write-Host "Data: $trainerData" -ForegroundColor Yellow
    
    $trainerResponse = Invoke-WebRequest -Uri "$baseUrl/v1/trainers" `
        -Method POST `
        -Headers @{
            'Content-Type' = 'application/json'
            'Accept' = 'application/json'
            'Authorization' = "Bearer $token"
        } `
        -Body $trainerData `
        -UseBasicParsing
    
    $trainerBody = $trainerResponse.Content | ConvertFrom-Json
    Write-Host "Creation status: $($trainerResponse.StatusCode)" -ForegroundColor Green
    Write-Host "Response:" -ForegroundColor Yellow
    $trainerBody | ConvertTo-Json -Depth 2 | Write-Host
    
} else {
    Write-Host "[FAIL] Login failed" -ForegroundColor Red
}
