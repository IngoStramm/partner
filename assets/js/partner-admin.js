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

    function partner_cliente_selected() {
        const cliente_select = document.getElementById('atendimento_post');
        if (typeof (cliente_select) === undefined || cliente_select === null) { return; }

        const marca_select = document.getElementById('atendimento_marca');
        const marca_select_placeholder = marca_select.innerHTML;

        cliente_select.addEventListener('change', function () {
            const cliente_selected = this.value;

            if (cliente_selected === '0') {
                marca_select.innerHTML = marca_select_placeholder;
                return;
            }

            // console.log(cliente_selected);
            // console.log(ajax_object.ajax_url);

            let is_disabled = cliente_select.dataset.disabled;
            if (is_disabled) {
                return;
            }
            cliente_select.dataset.disabled = '';

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
                }
                delete cliente_select.dataset.disabled;
            };

            xhr.send();

        });
    }
    partner_delete_transient_evt();
    partner_cliente_selected();
});