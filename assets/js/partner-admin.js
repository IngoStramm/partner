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
    partner_delete_transient_evt();
    partner_cliente_selected();
});