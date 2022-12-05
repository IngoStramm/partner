
const closePopup = () => {
    const existingPopups = document.querySelector('.partner-popup-wrapper');
    if (existingPopups) {
        existingPopups.remove();
        const html = document.documentElement;
        const body = document.body;
        html.style.overflow = '';
        body.style.overflow = '';
        wp.editor.remove('chamado-detalhamento-solicitacao');
        wp.editor.remove('chamado-detalhamento-resolucao');
    }
};

const popupCronogramaInit = () => {
    const partnerTriggerPopup = document.querySelectorAll('.partner-trigger-popup');
    const html = document.documentElement;
    const body = document.body;

    for (const link of partnerTriggerPopup) {
        link.addEventListener('click', e => {
            e.preventDefault();
            html.style.overflow = 'hidden';
            body.style.overflow = 'hidden';

            closePopup();

            const popupId = link.dataset.partnerPopupId;
            const getPopupContent = document.querySelector(`#${popupId}`).innerHTML;
            const popupContent = document.createElement('div');

            popupContent.classList.add('popup-content');
            popupContent.id = 'popup-content';
            popupContent.innerHTML = getPopupContent;

            const popup = document.createElement('div');

            popup.classList.add('partner-popup');
            popup.appendChild(popupContent);

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
};

const popupChamadosInit = () => {
    const partnerTriggersPopup = document.querySelectorAll('.partner-trigger-popup-chamados');

    for (const trigger of partnerTriggersPopup) {
        let link = '';
        if (trigger.tagName === 'A') {
            link = trigger;
        } else {
            link = trigger.querySelector('a');
        }
        clickEventChamado(link);
    }

};

const clickEventChamado = (link) => {
    link.addEventListener('click', (e) => {
        e.preventDefault();
        triggerPopupChamados(link);
    });
};

const triggerPopupChamados = function (link) {
    // link.addEventListener('click', e => {

    // e.preventDefault();

    closePopup();

    // verifica se o e.targethref possui "post_id" na string
    const checkQueryString = link.href.match(/post_id=(\d+)/);
    // pegar o parametro "post_id" query string do href e ignorar os outros parâmetros
    const postId = typeof (checkQueryString) !== undefined && checkQueryString !== null ? link.href.split('?')[1].split('=')[1] : null;
    const popup = document.createElement('div');
    popup.classList.add('partner-popup');

    const closePopupBtn = document.createElement('a');

    closePopupBtn.classList.add('partner-popup-close');
    closePopupBtn.innerHTML = '&times;';
    popup.appendChild(closePopupBtn);

    const popupContent = document.createElement('div');
    popupContent.classList.add('popup-content');
    popupContent.id = 'popup-content';

    const loading = document.createElement('div');
    loading.id = 'loading';
    loading.classList.add('loading');
    loading.textContent = 'Carregando...';
    popupContent.appendChild(loading);
    popup.appendChild(popupContent);


    const popupBackground = document.createElement('div');

    popupBackground.classList.add('partner-popup-wrapper');
    popupBackground.appendChild(popup);
    document.body.insertBefore(popupBackground, document.body.firstChild);

    const html = document.documentElement;
    const body = document.body;
    html.style.overflow = 'hidden';
    body.style.overflow = 'hidden';

    closePopupBtn.addEventListener('click', () => {
        closePopup();
    });

    popupBackground.addEventListener('click', (e) => {
        if (e.target.classList.contains('partner-popup-wrapper')) {
            closePopup();
        }
    });

    const chamado = partner_get_chamado(postId, popup, popupContent);

    // });
};

const partner_get_chamado = (post_id, popup, popupContent) => {
    const action = 'partner_get_chamado';
    const xhr = new XMLHttpRequest();
    const params = post_id ? `post_id=${post_id}` : '';
    xhr.responseType = 'json';
    xhr.open('POST', `${ajax_object.ajax_url}?action=${action}&${params}`);
    xhr.onload = function () {
        const response = xhr.response;

        if (xhr.status === 200) {

            if (!response.success) {
                console.log(response);
                return;
            }
            const loading = document.getElementById('loading');
            if (typeof (loading) !== 'undefined' && loading !== null) {
                loading.remove();
            }
            partner_set_chamado_form(response, post_id, popup, popupContent);

            wp.editor.initialize('chamado-detalhamento-solicitacao', { tinymce: true });
            wp.editor.initialize('chamado-detalhamento-resolucao', { tinymce: true });

            // tinymce.init({
            //     selector: '.chamado-textarea',
            //     plugins: [
            //         'a11ychecker', 'advlist', 'advcode', 'advtable', 'autolink', 'checklist', 'export',
            //         'lists', 'link', 'image', 'charmap', 'preview', 'anchor', 'searchreplace', 'visualblocks',
            //         'powerpaste', 'fullscreen', 'formatpainter', 'insertdatetime', 'media', 'table', 'help', 'wordcount'
            //     ],
            //     toolbar: 'undo redo | formatpainter casechange blocks | bold italic backcolor | ' +
            //         'alignleft aligncenter alignright alignjustify | ' +
            //         'bullist numlist checklist outdent indent | removeformat | a11ycheck code table help'
            // });

        }
    };

    xhr.send();

};

const partner_set_chamado_form = (response, post_id, popup, popupContent) => {
    // console.log(post_id);

    // form
    const chamado_cliente_id = parseInt(response.cliente);
    const chamado_marca = response.marca;
    const form = document.createElement('form');
    form.id = 'partner-chamado-form';
    form.classList.add('partner-chamado-form');

    let input_clientes;
    const clientes = response.clientes;

    if (post_id === null) {
        input_clientes = document.createElement('select');
        const default_option = document.createElement('option');
        default_option.value = '';
        default_option.innerHTML = 'Selecione um cliente';
        input_clientes.appendChild(default_option);
        for (const cliente of clientes) {
            const option = document.createElement('option');
            option.value = cliente.ID;
            option.innerHTML = cliente.post_title;
            if (chamado_cliente_id === cliente.ID) {
                option.selected = true;
            }
            input_clientes.appendChild(option);
        }

    } else {
        input_clientes = document.createElement('input');
        input_clientes.type = 'hidden';
        input_clientes.value = chamado_cliente_id;
        const title_cliente_name = document.createElement('h4');
        for (const cliente of clientes) {
            if (chamado_cliente_id === cliente.ID) {
                title_cliente_name.textContent = `${cliente.post_title}`;
            }
        }
        form.appendChild(title_cliente_name);
    }
    input_clientes.id = 'partner-chamado-cliente';
    input_clientes.name = 'partner-chamado-cliente';

    form.appendChild(input_clientes);

    // select marcas
    if (chamado_cliente_id) {
        partner_set_marcas_options(response, chamado_cliente_id, chamado_cliente_id, chamado_marca, form);
    }

    // verifica se o input_clientes é do tipo select
    if (input_clientes.tagName === 'SELECT') {
        input_clientes.addEventListener('change', function () {
            // remove o elemento com o id 'partner-chamado-marca'
            const previous_select_marcas = document.getElementById('partner-chamado-marca');
            if (typeof (previous_select_marcas !== 'undefined') && previous_select_marcas !== null) {
                previous_select_marcas.remove();
            }
            removeChamadoInputs();

            const selected_cliente_id = this.value;

            partner_set_marcas_options(response, chamado_cliente_id, selected_cliente_id, chamado_marca, form);
        });
    }

    // pega o evento submit do form
    form.addEventListener('submit', function (event) {
        event.preventDefault();
        tinyMCE.triggerSave();
        const formData = new FormData(form);
        const chamado = {};
        let error = null;
        for (const [key, value] of formData.entries()) {
            const currInput = document.getElementById(key);
            currInput.classList.remove('error');
            chamado[key] = value;
            if (!value) {
                error = true;
                currInput.classList.add('error');
            }
        }
        if (error) {
            return;
        }
        const loading = document.createElement('div');
        loading.id = 'loading';
        loading.classList.add('loading');
        loading.textContent = 'Processando...';
        // adiciona o loading no início do popup
        popupContent.insertBefore(loading, popupContent.firstChild);
        partner_save_chamado(chamado, post_id, form, popup);
    });

    popupContent.appendChild(form);
    popup.appendChild(popupContent);

};

const partner_set_marcas_options = (response, chamado_cliente_id, selected_cliente_id, chamado_marca, form) => {
    const marcas = response.marcas;
    const select_marcas = document.createElement('select');
    let selected_marca = null;
    select_marcas.id = 'partner-chamado-marca';
    select_marcas.name = 'partner-chamado-marca';

    if (!selected_cliente_id) {
        return;
    }
    const selected_option_marcas_array = marcas[selected_cliente_id];
    const default_option = document.createElement('option');
    default_option.value = '';
    default_option.textContent = 'Selecione uma marca';
    select_marcas.appendChild(default_option);
    for (const marca_cliente of selected_option_marcas_array) {
        const option = document.createElement('option');
        option.textContent = marca_cliente;
        option.value = marca_cliente;
        if (chamado_marca === marca_cliente) {
            option.selected = true;
        }
        select_marcas.appendChild(option);
    }
    select_marcas.addEventListener('change', () => {
        removeChamadoInputs();
        selected_marca = select_marcas.value;
        if (selected_marca) {
            addChamadoInputs(response, chamado_cliente_id, selected_cliente_id, chamado_marca, selected_marca, form);
            wp.editor.initialize('chamado-detalhamento-solicitacao', { tinymce: true });
            wp.editor.initialize('chamado-detalhamento-resolucao', { tinymce: true });

        }
    });
    form.appendChild(select_marcas);

    if (parseInt(chamado_cliente_id) === parseInt(selected_cliente_id)) {
        selected_marca = chamado_marca;
        addChamadoInputs(response, chamado_cliente_id, selected_cliente_id, chamado_marca, selected_marca, form);
    }
};

const addChamadoInputs = (response, chamado_cliente_id, selected_cliente_id, chamado_marca, selected_marca, form) => {

    const urgencias = response.urgencias;
    const users = response.users;
    const statuses = response.status;
    // console.log(chamado_marca, selected_marca);

    // Assunto
    const assuntoInput = document.createElement('input');
    assuntoInput.type = 'text';
    assuntoInput.name = 'chamado-assunto';
    assuntoInput.id = 'chamado-assunto';
    assuntoInput.className = 'chamado-input';
    assuntoInput.placeholder = 'Assunto';
    assuntoInput.required = true;

    // Detalhamento da Solicitação
    const detalhamentoSolicitacaoInput = document.createElement('textarea');
    detalhamentoSolicitacaoInput.name = 'chamado-detalhamento-solicitacao';
    detalhamentoSolicitacaoInput.id = 'chamado-detalhamento-solicitacao';
    detalhamentoSolicitacaoInput.className = 'chamado-textarea';
    detalhamentoSolicitacaoInput.placeholder = 'Detalhamento da Solicitação';
    detalhamentoSolicitacaoInput.rows = '5';
    detalhamentoSolicitacaoInput.required = true;

    // Label da data de solicitação
    const dataSolicitacaoLabel = document.createElement('label');
    dataSolicitacaoLabel.id = 'chamado-data-solicitacao-label';
    dataSolicitacaoLabel.htmlFor = 'chamado-data-solicitacao';
    dataSolicitacaoLabel.innerText = 'Data da solicitação';

    // Data da solicitação
    const dataSolicitacaoInput = document.createElement('input');
    dataSolicitacaoInput.name = 'chamado-data-solicitacao';
    dataSolicitacaoInput.id = 'chamado-data-solicitacao';
    dataSolicitacaoInput.className = 'chamado-input';
    dataSolicitacaoInput.type = 'datetime-local';
    dataSolicitacaoInput.required = true;

    dataSolicitacaoLabel.appendChild(dataSolicitacaoInput);

    // Label da data para a entrega
    const dataEntregaLabel = document.createElement('label');
    dataEntregaLabel.id = 'chamado-data-entrega-label';
    dataEntregaLabel.htmlFor = 'chamado-data-entrega';
    dataEntregaLabel.innerText = 'Data para a entrega';

    // Data da entrega
    const dataEntregaInput = document.createElement('input');
    dataEntregaInput.name = 'chamado-data-entrega';
    dataEntregaInput.id = 'chamado-data-entrega';
    dataEntregaInput.className = 'chamado-input';
    dataEntregaInput.type = 'datetime-local';
    dataEntregaInput.required = true;

    dataEntregaLabel.appendChild(dataEntregaInput);

    // Urgência
    const urgenciaSelect = document.createElement('select');
    urgenciaSelect.name = 'chamado-urgencia';
    urgenciaSelect.id = 'chamado-urgencia';
    urgenciaSelect.className = 'chamado-select';
    urgenciaSelect.required = true;

    const urgenciaDefaultOption = document.createElement('option');
    urgenciaDefaultOption.value = '';
    urgenciaDefaultOption.textContent = 'Selecione o nível de urgência';
    urgenciaSelect.appendChild(urgenciaDefaultOption);

    for (const urgencia of urgencias) {
        const option = document.createElement('option');
        option.value = urgencia.term_id;
        option.textContent = urgencia.name;
        urgenciaSelect.appendChild(option);
    }

    // Ponto focal
    const pontoFocalSelect = document.createElement('select');
    pontoFocalSelect.id = 'chamado-ponto-focal';
    pontoFocalSelect.name = 'chamado-ponto-focal';
    pontoFocalSelect.className = 'chamado-select';
    pontoFocalSelect.required = true;

    for (const k in users) {
        const option = document.createElement('option');
        option.value = parseInt(k) === 0 ? '' : parseInt(k);
        option.textContent = users[k];
        pontoFocalSelect.appendChild(option);
    }

    // Status
    const statusSelect = document.createElement('select');
    statusSelect.name = 'chamado-status';
    statusSelect.id = 'chamado-status';
    statusSelect.className = 'chamado-select';
    statusSelect.required = true;

    const statusDefaultOption = document.createElement('option');
    statusDefaultOption.value = '';
    statusDefaultOption.textContent = 'Selecione o status';
    statusSelect.appendChild(statusDefaultOption);

    for (const status of statuses) {
        const option = document.createElement('option');
        option.value = status.term_id;
        option.textContent = status.name;
        statusSelect.appendChild(option);
    }

    // Detalhamento da Resolução
    const detalhamentoResolucaoInput = document.createElement('textarea');
    detalhamentoResolucaoInput.name = 'chamado-detalhamento-resolucao';
    detalhamentoResolucaoInput.id = 'chamado-detalhamento-resolucao';
    detalhamentoResolucaoInput.className = 'chamado-textarea';
    detalhamentoResolucaoInput.placeholder = 'Detalhamento da Resolução';
    detalhamentoResolucaoInput.rows = '5';
    detalhamentoResolucaoInput.required = true;

    // Submit button
    const submitButton = document.createElement('button');
    submitButton.type = 'submit';
    submitButton.className = 'chamado-button';
    submitButton.id = 'chamado-button';
    submitButton.textContent = 'Enviar';

    // Se o chamado já existir
    if (response.chamado) {
        // Se for o cliente do chamado cadastrado
        if ((parseInt(chamado_cliente_id) === parseInt(selected_cliente_id)) && (chamado_marca === selected_marca)) {
            // Define os valores dos campos do chamado
            assuntoInput.value = response.chamado.assunto;
            detalhamentoSolicitacaoInput.value = response.chamado.detalhamento_solicitacao;
            dataSolicitacaoInput.value = response.chamado.data_solicitacao;
            dataEntregaInput.value = response.chamado.data_entrega;
            urgenciaSelect.value = response.chamado.urgencia;
            pontoFocalSelect.value = response.chamado.ponto_focal;
            statusSelect.value = response.chamado.status;
            detalhamentoResolucaoInput.value = response.chamado.detalhamento_resolucao;
        }
    }

    form.appendChild(assuntoInput);
    form.appendChild(detalhamentoSolicitacaoInput);
    form.appendChild(dataSolicitacaoLabel);
    form.appendChild(dataEntregaLabel);
    form.appendChild(urgenciaSelect);
    form.appendChild(pontoFocalSelect);
    form.appendChild(statusSelect);
    form.appendChild(detalhamentoResolucaoInput);
    form.appendChild(submitButton);
};

const removeChamadoInputs = () => {
    const assuntoInput = document.getElementById('chamado-assunto');
    if (typeof (assuntoInput) !== 'undefined' && assuntoInput !== null) {
        assuntoInput.remove();
    }
    const detalhamentoSolicitacaoInput = document.getElementById('chamado-detalhamento-solicitacao');
    if (typeof (detalhamentoSolicitacaoInput) !== 'undefined' && detalhamentoSolicitacaoInput !== null) {
        detalhamentoSolicitacaoInput.remove();
    }
    const dataSolicitacaoLabel = document.getElementById('chamado-data-solicitacao-label');
    if (typeof (dataSolicitacaoLabel) !== 'undefined' && dataSolicitacaoLabel !== null) {
        dataSolicitacaoLabel.remove();
    }
    const dataEntregaLabel = document.getElementById('chamado-data-entrega-label');
    if (typeof (dataEntregaLabel) !== 'undefined' && dataEntregaLabel !== null) {
        dataEntregaLabel.remove();
    }
    const urgenciaSelect = document.getElementById('chamado-urgencia');
    if (typeof (urgenciaSelect) !== 'undefined' && urgenciaSelect !== null) {
        urgenciaSelect.remove();
    }
    const pontoFocalSelect = document.getElementById('chamado-ponto-focal');
    if (typeof (pontoFocalSelect) !== 'undefined' && pontoFocalSelect !== null) {
        pontoFocalSelect.remove();
    }
    const statusSelect = document.getElementById('chamado-status');
    if (typeof (statusSelect) !== 'undefined' && statusSelect !== null) {
        statusSelect.remove();
    }
    const detalhamentoResolucaoInput = document.getElementById('chamado-detalhamento-resolucao');
    if (typeof (detalhamentoResolucaoInput) !== 'undefined' && detalhamentoResolucaoInput !== null) {
        detalhamentoResolucaoInput.remove();
    }
    const submitButton = document.getElementById('chamado-button');
    if (typeof (submitButton) !== 'undefined' && submitButton !== null) {
        submitButton.remove();
    }
    wp.editor.remove('chamado-detalhamento-solicitacao');
    wp.editor.remove('chamado-detalhamento-resolucao');

};

const partner_save_chamado = (chamado, post_id, form, popup) => {
    const action = 'partner_save_chamado';
    const xhr = new XMLHttpRequest();
    const params = new URLSearchParams(chamado).toString();
    let query = `${ajax_object.ajax_url}?action=${action}&${params}`;
    query += `&partner_nonce=${ajax_object.partner_nonce}`;
    if (typeof (post_id) !== 'undefined' && post_id !== null) {
        query += `&post_id=${post_id}`;
    }
    xhr.responseType = 'json';
    xhr.open('POST', query);
    xhr.onload = function () {
        const response = xhr.response;
        // console.log(response);

        if (xhr.status === 200) {

            if (!response.success) {
                const loading = document.getElementById('loading');
                if (typeof (loading) !== 'undefined' && loading !== null) {
                    loading.remove();
                    const error = document.createElement('div');
                    error.id = 'error';
                    error.classList.add('error');
                    error.textContent = 'Ocorreu um erro ao tentar salvar o chamado.';
                    // adiciona o error no início do popup
                    popup.insertBefore(error, popup.firstChild);
                    console.log(response);
                }
                return;
            }

            form.remove();
            //recarrega a página
            // closePopup();
            location.reload();
            // partner_atualiza_grid_list();
        }
    };

    xhr.send();

};

document.addEventListener('DOMContentLoaded', function () {
    popupCronogramaInit();
    popupChamadosInit();
});

jQuery(document).on('tinymce-editor-setup', function (event, editor) {
    editor.settings.toolbar1 = 'bold,italic,underline,blockquote,strikethrough,bullist,numlist,alignleft,aligncenter,alignright,undo,redo,link'; //Teeny -fullscreen
});