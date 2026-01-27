/* ------------------------------------------------------------------------------
 *
 *  # Dashboard page
 *
 * ---------------------------------------------------------------------------- */


// Setup module
// ------------------------------
function SearchNonPersistedBeneficiaries() {
    const searchBar = document.getElementById('search-non-persisted');
    const beneficiaries = document.querySelectorAll('.beneficiary-non-persisted');

    searchBar.addEventListener('input', (e) => {
        const search = e.target.value.toLowerCase();

        beneficiaries.forEach(beneficiary => {
            const name = beneficiary.dataset.name;
            if (name.includes(search)) {
                beneficiary.style.opacity = '0';
                beneficiary.style.display = '';
                setTimeout(() => {
                    beneficiary.style.transition = 'opacity 0.3s ease-in-out';
                    beneficiary.style.opacity = '1';
                }, 10);
            } else {
                beneficiary.style.transition = 'opacity 0.3s ease-in-out';
                beneficiary.style.opacity = '0';
                setTimeout(() => {
                    beneficiary.style.display = 'none';
                }, 300);
            }
        });
    });
}

function SearchPersistedBeneficiaries() {
    const searchBar = document.getElementById('search-persisted');

    searchBar.addEventListener('input', (e) => {
        const search = e.target.value.toLowerCase();
        loadDatabaseBeneficiaries(search);
    });
}

async function loadDatabaseBeneficiaries(search = '') {
    try {
        let url = '/api/beneficiaries';
        if (search) {
            url += `?name=${encodeURIComponent(search)}`;
        }
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/ld+json'
            }
        });

        if (!response.ok) throw new Error('Error loading beneficiaries');

        const data = await response.json();
        const beneficiaries = data['member'];

        const container = document.querySelector('#beneficiaries-container');
        container.innerHTML = '';

        beneficiaries.forEach(beneficiary => {
            container.innerHTML += createBeneficiaryCard(beneficiary);
        });

    } catch (error) {
        console.error('Error loading beneficiaries:', error);
        showError('Failed to load beneficiaries');
    }
}

function initDeleteModal() {
    $('#modal_beneficiary_deletion').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const beneficiaryId = button.data('id');
        const beneficiaryName = button.data('name');

        const modal = $(this);
        modal.find('#beneficiary-delete-id').val(beneficiaryId);
        modal.find('#beneficiary-delete-name').text(beneficiaryName);
    });

    $('#modal_beneficiary_modification').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const beneficiaryId = button.data('id');
        const beneficiaryName = button.data('name');

        const modal = $(this);
        modal.find('#beneficiary-update-id').val(beneficiaryId);
        modal.find('#beneficiary-update-name').val(beneficiaryName);
    });
}

function createBeneficiaryCard(beneficiary) {
    const avatarEndpoint = 'https://api.dicebear.com/8.x/avataaars/svg?eyes=hearts,happy,default,side,wink&mouth=smile,default,twinkle,serious&seed=';
    const formatted_date = beneficiary.createdAt ? new Intl.DateTimeFormat('en-EN', {dateStyle: 'short', timeStyle: 'short'}).format(new Date(beneficiary.createdAt)) : 'undefined';

    return `
        <div class="col-xl-2 col-sm-6 beneficiary-persisted" data-name="${beneficiary.name.toLowerCase()}" data-id="${beneficiary.id}">
            <div class="card">
                <div class="card-body text-center">
                    <div class="card-img-actions d-inline-block mb-3">
                        <img class="img-fluid rounded-circle" src="${avatarEndpoint + encodeURIComponent(beneficiary.name)}" width="170" height="170" alt="avatar-${beneficiary.name}">
                    </div>
                    <h6 class="font-weight-semibold mb-0">${beneficiary.name}</h6>
                    <span class="d-block text-muted">creator email: ${beneficiary.creatorEmail}</span>
                    <span class="d-block text-muted">creation date: ${formatted_date}</span>
                    <div class="list-icons list-icons-extended mt-3">
                        <a href="javascript:;" class="list-icons-item" data-popup="tooltip" title="Modify" data-id="${beneficiary.id}" data-name="${beneficiary.name}" data-toggle="modal" data-target="#modal_beneficiary_modification"><i class="icon-pencil7"></i></a>
                        <a href="javascript:;" class="list-icons-item" data-popup="tooltip" title="Delete" data-id="${beneficiary.id}" data-name="${beneficiary.name}" data-toggle="modal" data-target="#modal_beneficiary_deletion"><i class="icon-trash"></i></a>
                    </div>
                </div>
            </div>
        </div>
    `;
}

// Create Beneficiary via API
// ------------------------------
function createBeneficiary() {
    const createBtn = document.getElementById('beneficiary-create-btn');
    if (createBtn) {
        createBtn.addEventListener('click', async function () {
            const nameInput = document.querySelector('input[name="beneficiary[name]"]');
            if (!nameInput) {
                console.log('Champ name introuvable.');
                return;
            }
            const name = nameInput.value.trim();
            if (!name) {
                alert('Please, enter a name for the beneficiary.');
                return;
            }

            try {
                const response = await fetch('/api/beneficiaries', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/ld+json',
                        'Content-Type': 'application/ld+json'
                    },
                    body: JSON.stringify({ name })
                });

                if (response.ok) {
                    $('#modal_form_vertical').modal('hide');
                    loadDatabaseBeneficiaries();
                } else {
                    const data = await response.json();
                    console.log('Erreur API: ' + (data.detail || response.statusText));
                }
            } catch (e) {
                console.log('Erreur réseau: ' + e.message);
            }
        });
    }
}

// Delete Beneficiary via API
// ------------------------------
function deleteBeneficiary() {
    const deleteBtn = document.getElementById('beneficiary-delete-btn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', async function () {
            const deleteId = document.getElementById('beneficiary-delete-id').value;
            try {
                const response = await fetch(`/api/beneficiaries/${deleteId}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/ld+json',
                    }
                });

                if (!response.ok) throw new Error('Error deleting beneficiary');
                $('#modal_beneficiary_deletion').modal('hide');
                loadDatabaseBeneficiaries();
            } catch (e) {
                console.log('Error: ' + e.message);
            }
        });
    }
}


// Initialize module
// ------------------------------
document.addEventListener('DOMContentLoaded', function () {
    SearchNonPersistedBeneficiaries();
    SearchPersistedBeneficiaries();
    loadDatabaseBeneficiaries();
    initDeleteModal();
    createBeneficiary();
    deleteBeneficiary();
});
