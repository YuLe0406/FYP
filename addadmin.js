document.addEventListener('DOMContentLoaded', function () {
    const adminForm = document.querySelector('.admin-form');
    const adminList = document.querySelector('.admin-list');

    // Function to add a new admin
    adminForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const adminName = document.getElementById('adminName').value;
        const adminEmail = document.getElementById('adminEmail').value;

        // Create a new admin entry
        const adminItem = document.createElement('li');
        adminItem.className = 'admin-item';

        adminItem.innerHTML = `
            <span class="admin-name">${adminName}</span>
            <span class="admin-email">${adminEmail}</span>
            <div class="admin-actions">
                <button class="edit-btn">Edit</button>
                <button class="delete-btn">Delete</button>
            </div>
        `;

        // Add to the list
        adminList.appendChild(adminItem);

        // Clear the form
        adminForm.reset();

        // Add event listeners for edit and delete buttons
        const editBtn = adminItem.querySelector('.edit-btn');
        const deleteBtn = adminItem.querySelector('.delete-btn');

        editBtn.addEventListener('click', () => {
            document.getElementById('adminName').value = adminName;
            document.getElementById('adminEmail').value = adminEmail;
            adminList.removeChild(adminItem);
        });

        deleteBtn.addEventListener('click', () => {
            adminList.removeChild(adminItem);
        });
    });
});