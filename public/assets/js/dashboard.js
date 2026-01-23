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

async function loadDatabaseBeneficiaries() {
    try {
        const response = await fetch('/api/beneficiaries', {
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
                </div>
            </div>
        </div>
    `;
}


// Initialize module
// ------------------------------
document.addEventListener('DOMContentLoaded', function () {
    SearchNonPersistedBeneficiaries();
    loadDatabaseBeneficiaries();
});
