document.addEventListener('DOMContentLoaded', function () {
    const partnerTriggerPopup = document.querySelectorAll('.partner-trigger-popup');
    for (const link of partnerTriggerPopup) {
        link.addEventListener('click', e => {
            e.preventDefault();
            const closePopup = () => {
                const existingPopups = document.querySelector('.partner-popup-wrapper');
                if (existingPopups) {
                    existingPopups.remove();
                }
            };
            closePopup();
            const popupId = link.dataset.partnerPopupId;
            const popupContent = document.querySelector(`#${popupId}`).innerHTML;
            const popup = document.createElement('div');
            popup.classList.add('partner-popup');
            popup.innerHTML = popupContent;
            const closePopupBtn = document.createElement('a');
            closePopupBtn.classList.add('partner-popup-close');
            closePopupBtn.innerHTML = '&times;';
            popup.appendChild(closePopupBtn);
            const popupBackground = document.createElement('div');
            popupBackground.classList.add('partner-popup-wrapper');
            popupBackground.appendChild(popup);
            document.body.insertBefore(popupBackground, document.body.firstChild);
            closePopupBtn.addEventListener('click', () => {
                closePopup();
            });

            popupBackground.addEventListener('click', (e) => {
                if (e.target.classList.contains('partner-popup-wrapper')) {
                    closePopup();
                }
            });
        });
    }
});