// --- Core function to send text data to PHP Backend ---
function sendToBackend(inputId, resultId, action) {
    const inputElement = document.getElementById(inputId);
    const resultDiv = document.getElementById(resultId);
    const inputValue = inputElement ? inputElement.value.trim() : '';

    if (!inputValue) {
        resultDiv.textContent = `Error: Please enter data for ${action}.`;
        return;
    }
    
    resultDiv.textContent = `📡 Analyzing data via Backend (${action})... Please wait. (Telegram Alert is being prepared...)`;

    const dataToSend = new URLSearchParams({
        action: action,
        input_value: inputValue
    });

    fetch('process.php', {
        method: 'POST',
        body: dataToSend,
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    })
    .then(response => response.text())
    .then(result => {
        resultDiv.textContent = result;
    })
    .catch(error => {
        resultDiv.textContent = `CRITICAL Error: Could not reach the server! Check if PHP server is running.`;
    });
}

// --- Function for File Upload (Special Case) ---
function uploadFile() {
    const fileInput = document.getElementById('fileToUpload');
    const resultDiv = document.getElementById('uploadResult');
    
    if (fileInput.files.length === 0) {
        resultDiv.textContent = "Error: Please select a file to upload.";
        return;
    }

    const file = fileInput.files[0];
    resultDiv.textContent = `📡 Uploading ${file.name} to PHP/Python Scanner...`;

    const formData = new FormData();
    formData.append('action', 'uploadFile');
    formData.append('file', file);

    fetch('process.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(result => {
        resultDiv.textContent = result;
    })
    .catch(error => {
        resultDiv.textContent = `CRITICAL Error during upload: Check Termux storage permissions.`;
    });
}
