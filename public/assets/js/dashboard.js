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


// Initialize module
// ------------------------------
document.addEventListener('DOMContentLoaded', function() {
    SearchNonPersistedBeneficiaries();
});
