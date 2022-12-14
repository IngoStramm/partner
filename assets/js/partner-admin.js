document.addEventListener('DOMContentLoaded', function () {

    function partner_delete_transient_evt() {
        const btnWrapper = document.getElementById('wp-admin-bar-partner-delete-transients-btn-wrapper');

        if (!btnWrapper) {
            return;
        }

        const btn = btnWrapper.getElementsByTagName('a')[0];

        if (!btn) {
            return;
        }

        btn.addEventListener('click', function (e) {

            e.preventDefault();
            console.log('click');
            let is_disabled = btn.dataset.disabled;
            if (is_disabled) {
                return;
            }
            btn.dataset.disabled = '';
            const action = 'partner_delete_transient';

            const xhr = new XMLHttpRequest();
            xhr.responseType = 'json';
            xhr.open('POST', ajax_object.ajax_url + '?action=' + action);
            xhr.onload = function () {
                const response = xhr.response;
                // console.log(response);
                if (xhr.status === 200) {
                    alert(response.msg);
                }
                delete btn.dataset.disabled;
            };

            xhr.send();
        });

    }

    function partner_get_marca(cliente_select, marca_select) {
        const cliente_selected = cliente_select.value;
        const action = 'partner_get_cliente_marcas';

        const xhr = new XMLHttpRequest();
        const params = `cliente_id=${cliente_selected}`;
        xhr.responseType = 'json';
        xhr.open('POST', `${ajax_object.ajax_url}?action=${action}&${params}`);
        xhr.onload = function () {
            const response = xhr.response;

            if (xhr.status === 200) {
                if (typeof (marca_select) === undefined || marca_select === null) { return; }

                if (!response.success) {
                    console.log(response);
                    return;
                }
                marca_select.innerHTML = response.options;
                if (chamado_marca.value) {
                    marca_select.value = chamado_marca.value;
                }

            }
            delete cliente_select.dataset.disabled;
        };

        xhr.send();

    }

    function partner_cliente_selected() {
        const cliente_select = document.getElementById('chamado_post');
        if (typeof (cliente_select) === undefined || cliente_select === null) { return; }

        const marca_select = document.getElementById('chamado_marca_select');
        const marca_select_placeholder = marca_select.innerHTML;

        const chamado_marca = document.getElementById('chamado_marca');

        partner_get_marca(cliente_select, marca_select);

        cliente_select.addEventListener('change', function () {
            const cliente_selected = this.value;
            chamado_marca.value = '';

            if (cliente_selected === '0') {
                marca_select.innerHTML = marca_select_placeholder;
                return;
            }

            let is_disabled = cliente_select.dataset.disabled;
            if (is_disabled) {
                return;
            }
            cliente_select.dataset.disabled = '';

            partner_get_marca(cliente_select, marca_select);

        });

        marca_select.addEventListener('change', function () {
            chamado_marca.value = this.value;
        });


    }

    function partner_set_term_for_select(select, checklist) {
        const select_value = select.value;
        const radio_options = checklist.querySelectorAll('input[type="radio"]');
        for (let i = 0; i < radio_options.length; i++) {
            const radio_option = radio_options[i];
            if (radio_option.value === select_value) {
                radio_option.checked = true;
            } else {
                radio_option.checked = false;
            }
        }

    }

    function partner_set_term_event(select_id, checklist_wrapper_id) {

        const select = document.getElementById(select_id);
        if (typeof (select) === 'undefined' || select === null) {
            return;
        }

        const checklist_wrapper = document.getElementById(checklist_wrapper_id);
        if (typeof (checklist_wrapper) === 'undefined' || checklist_wrapper === null) {
            return;
        }

        partner_set_term_for_select(select, checklist_wrapper);

        select.addEventListener('change', () => {
            partner_set_term_for_select(select, checklist_wrapper);
        });
    }

    function partner_phone_mask() {
        const phone_inputs = document.querySelectorAll('.phone-mask');
        for (let input of phone_inputs) {
            if (typeof (input) === 'undefined' || input === null) {
                return;
            }
            input.addEventListener('keyup', (e) => {
                e.target.value = e.target.value.replace(/\D/g, '');
                e.target.value = e.target.value.replace(/(^\d{2})(\d)/, '($1) $2');
                e.target.value = e.target.value.replace(/(\d{4,5})(\d{4}$)/, '$1-$2');
            });
        }
    }
    partner_set_term_event('chamado_urgencia', 'urgenciachecklist');
    partner_set_term_event('chamado_status', 'status-chamadochecklist');
    partner_set_term_event('chamado_etapa', 'etapachecklist');
    partner_delete_transient_evt();
    partner_cliente_selected();
    partner_phone_mask();
});