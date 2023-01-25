let completed_fields = [];
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
    completed_fields = [];
};

const wpEditorSettings = () => {
    return {
        tinymce: true,
        quicktags: false,
        mediaButtons: false
    };
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
// quando o popup está abrindo
// muda o filtro para qualquer opção diferente da atual
// quando o popup está fechando
// volta a para a opção original
const changeSortSelect = () => {

    // Verifica se o filtro existe
    const jetSort = document.querySelector('.jet-sorting-select');
    if (typeof (jetSort) === 'undefined' || jetSort === null) {
        return;
    }

    // Pega a opção atual
    const currOptionValue = jetSort.value;

    // Pega todas as opções do filtro (select)
    const sortOptions = jetSort.options;

    // define como null a próxima opção (será verificado mais a frente)
    let nextOptionValue = null;

    // pega a opção anterior nas propriedades do filtro (select)
    let oldValue = jetSort.dataset.oldValue;

    // verifica se está abrindo ou fechando o popup nas opções do filtro (select)
    let popupStatus = jetSort.dataset.popupStatus;

    // Se o popup estiver aberto, significa que está fechando o popup
    if (popupStatus === 'opened') {

        // salva o status do popup para fechado nas propriedades do filtro (select)
        jetSort.setAttribute('data-popup-status', 'closed');

        // define a próxima opção do filtro (select) como a opção anterior
        nextOptionValue = oldValue;
    } else { // Se o popup estiver fechado, está abrindo o popup

        // salva o status do popup para aberto nas propriedades do filtro (select)
        jetSort.setAttribute('data-popup-status', 'opened');

        // salva o valor atual como a opção anterior nas propriedades do filtro (select)
        jetSort.setAttribute('data-old-value', currOptionValue);

        for (const option of sortOptions) {
            // procura a primeira ocorrência da próxima opção que seja diferente da atual
            if (option.value !== currOptionValue) {
                nextOptionValue = option.value;
                break;
            }
        }
    }

    // muda a opção selecionada para a próxima opção do filtro (select)
    jetSort.value = nextOptionValue;

    // dispara o evento de mudança de opção do select
    jetSort.dispatchEvent(new Event('change'));
};

const triggerPopupChamados = function (mode, postId = null) {

    closePopup();
    changeSortSelect();

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

    setAHrefToOpenInNewTab();

    const html = document.documentElement;
    const body = document.body;
    html.style.overflow = 'hidden';
    body.style.overflow = 'hidden';

    closePopupBtn.addEventListener('click', () => {
        closePopup();
        changeSortSelect();
    });

    popupBackground.addEventListener('click', (e) => {
        if (e.target.classList.contains('partner-popup-wrapper')) {
            closePopup();
            changeSortSelect();
        }
    });

    const chamado = partner_get_chamado(mode, postId, popup, popupContent);

    // });
};

const partner_get_chamado = (mode, post_id, popup, popupContent) => {
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
            if (mode === 'view') {
                partner_view_chamado(response, post_id, popup, popupContent);
            } else {
                partner_set_chamado_form(response, post_id, popup, popupContent);
            }

            const settings = wpEditorSettings();

            wp.editor.initialize('chamado-detalhamento-solicitacao', settings);
            wp.editor.initialize('chamado-detalhamento-resolucao', settings);

            setAHrefToOpenInNewTab();


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

const partner_view_chamado = (response, post_id, popup, popupContent) => {
    if (typeof (response.chamado) === 'undefined' || response.chamado === null) {
        console.log(response);
        return;
    }
    const chamado = response.chamado;
    const currClienteId = parseInt(chamado.cliente);
    const clientes = response.clientes;
    let currCliente = null;
    for (const cliente of clientes) {
        if (cliente.ID === currClienteId) {
            currCliente = cliente;
        }
    }
    const currUrgenciaId = parseInt(chamado.urgencia);
    const urgencias = response.urgencias;
    let currUrgencia = null;
    for (const urgencia of urgencias) {
        if (urgencia.term_id === currUrgenciaId) {
            currUrgencia = urgencia;
        }
    }
    const currStatId = parseInt(chamado.status);
    const stats = response.status;
    let currStatus = null;
    for (const stat of stats) {
        if (stat.term_id === currStatId) {
            currStatus = stat;
        }
    }
    const currEtapaId = parseInt(chamado.etapa);
    const etapas = response.etapas;
    let currEtapa = null;
    for (const etapa of etapas) {
        if (etapa.term_id === currEtapaId) {
            currEtapa = etapa;
        }
    }

    const profissional = chamado.profissional.name;

    const sucessoCliente = chamado.sucesso_cliente.name;
    const contatoEmergencia = chamado.contato_emergencia.name;

    // const clienteH4 = document.createElement('h4');
    // clienteH4.classList.add('cliente-title');
    // clienteH4.textContent = currCliente.post_title;
    // popupContent.appendChild(clienteH4);

    popupContent.classList.add('chamado-view');

    const aside = document.createElement('aside');
    aside.id = 'chamado-aside';
    aside.classList.add('chamado-aside');

    // col 1

    const asideColumn1 = document.createElement('div');
    asideColumn1.id = 'chamado-aside-column-1';
    asideColumn1.classList.add('chamado-aside-column');

    const idWrapper = document.createElement('div');
    idWrapper.classList.add('chamado-id');
    idWrapper.innerHTML = `<h3>#${chamado.id}</h3>`;
    asideColumn1.appendChild(idWrapper);

    const marcaWrapper = document.createElement('div');
    marcaWrapper.classList.add('chamado-marca');
    marcaWrapper.innerHTML = `<h5>Marca</h5><h3>${chamado.marca}</h3>`;
    asideColumn1.appendChild(marcaWrapper);

    const assuntoWrapper = document.createElement('div');
    assuntoWrapper.classList.add('chamado-assunto');
    assuntoWrapper.innerHTML = `<h5>Assunto</h5><h3>${chamado.assunto}</h3>`;
    asideColumn1.appendChild(assuntoWrapper);
    aside.appendChild(asideColumn1);

    // col 2

    const asideColumn2 = document.createElement('div');
    asideColumn2.id = 'chamado-aside-column-2';
    asideColumn2.classList.add('chamado-aside-column');

    const dataSolicitacaoWrapper = document.createElement('div');
    dataSolicitacaoWrapper.classList.add('chamado-data-solicitacao');
    const dataSolicitacaoFormatada = new Date(chamado.data_solicitacao);

    let mesAbreviadoSolicitacao = dataSolicitacaoFormatada.toLocaleString('pt-BR', { month: 'short' });
    mesAbreviadoSolicitacao = mesAbreviadoSolicitacao.slice(0, -1);

    const diaNumericoSolicitacao = dataSolicitacaoFormatada.getDate();

    const horaMinutoSolicitacao = dataSolicitacaoFormatada.toLocaleString('pt-BR', { hour: 'numeric', minute: 'numeric' });

    dataSolicitacaoWrapper.innerHTML = `<h5>Data da Solicitação</h5><p><span class="dia">${diaNumericoSolicitacao}</span><span class="mes">${mesAbreviadoSolicitacao}</span><span class="hora">${horaMinutoSolicitacao}</span></p>`;

    // dataSolicitacaoWrapper.innerHTML = `<h5>Data da Solicitação</h5><h3>${dataSolicitacaoFormatada.toLocaleDateString('pt-BR', { hour: 'numeric', minute: 'numeric', hour12: false })}</h3>`;
    // dataSolicitacaoWrapper.innerHTML = `<h5>Data da Solicitação</h5><h3>${dataSolicitacaoExibicao}</h3>`;
    asideColumn2.appendChild(dataSolicitacaoWrapper);

    // const dataUltimaModificacaoWrapper = document.createElement('div');
    // dataUltimaModificacaoWrapper.classList.add('chamado-data-ultima-modificacao');
    // const dataUltimaModificacaoFormatada = new Date(chamado.post_modified);
    // dataUltimaModificacaoWrapper.innerHTML = `<h5>Última atualização:</strong><h3>${dataUltimaModificacaoFormatada.toLocaleDateString('pt-BR', { hour: 'numeric', minute: 'numeric', hour12: false })}</h3>`;
    // asideColumn2.appendChild(dataUltimaModificacaoWrapper);

    const previsaoEntregaWrapper = document.createElement('div');
    previsaoEntregaWrapper.classList.add('chamado-previsao-entrega');
    const previsaoEntregaFormatada = new Date(chamado.data_entrega);

    let mesAbreviadoEntrega = previsaoEntregaFormatada.toLocaleString('pt-BR', { month: 'short' });
    mesAbreviadoEntrega = mesAbreviadoEntrega.slice(0, -1);

    const diaNumericoEntrega = previsaoEntregaFormatada.getDate();

    const horaMinutoEntrega = previsaoEntregaFormatada.toLocaleString('pt-BR', { hour: 'numeric', minute: 'numeric' });

    previsaoEntregaWrapper.innerHTML = `<h5>Previsão de entrega</h5><p><span class="dia">${diaNumericoEntrega}</span><span class="mes">${mesAbreviadoEntrega}</span><span class="hora">${horaMinutoEntrega}</span></p>`;

    // previsaoEntregaWrapper.innerHTML = `<h5>Previsão de entrega:</h5><h3>${previsaoEntregaFormatada.toLocaleDateString('pt-BR', { hour: 'numeric', minute: 'numeric', hour12: false })}</h3>`;
    asideColumn2.appendChild(previsaoEntregaWrapper);

    aside.appendChild(asideColumn2);

    // col 3

    const asideColumn3 = document.createElement('div');
    asideColumn3.id = 'chamado-aside-column-3';
    asideColumn3.classList.add('chamado-aside-column');

    const tagsUrgenciaWrapper = document.createElement('div');
    tagsUrgenciaWrapper.classList.add('chamado-urgencia');
    tagsUrgenciaWrapper.innerHTML = `
    <h5>Tags:</h5>
    <ul class="chamado-tags">
    <li><span class="chamado-label" style="background-color: ${currUrgencia.cor}">${currUrgencia.name}</span></li>
    <li><span class="chamado-label" style="background-color: ${currStatus.cor}">${currStatus.name}</span></li>
    </ul>`;
    asideColumn3.appendChild(tagsUrgenciaWrapper);
    aside.appendChild(asideColumn3);

    popupContent.appendChild(aside);

    // content

    const content = document.createElement('div');
    content.id = 'chamado-content';
    content.classList.add('chamado-content');

    const detalhamentoSolicitacaoP = document.createElement('p');
    detalhamentoSolicitacaoP.classList.add('chamado-detalhamento-solicitacao-title');
    detalhamentoSolicitacaoP.innerHTML = `<h5>Solicitação</h5>`;
    content.appendChild(detalhamentoSolicitacaoP);

    const detalhamentoSolicitacaoDiv = document.createElement('div');
    detalhamentoSolicitacaoDiv.classList.add('chamado-detalhamento-solicitacao-text');
    detalhamentoSolicitacaoDiv.innerHTML = chamado.detalhamento_solicitacao;
    content.appendChild(detalhamentoSolicitacaoDiv);

    const detalhamentoResolucaoP = document.createElement('p');
    detalhamentoResolucaoP.classList.add('chamado-detalhamento-resolucao-title');
    detalhamentoResolucaoP.innerHTML = `<h5>Resolução</h5>`;
    content.appendChild(detalhamentoResolucaoP);

    const detalhamentoResolucaoDiv = document.createElement('div');
    detalhamentoResolucaoDiv.classList.add('chamado-detalhamento-resolucao-text');
    detalhamentoResolucaoDiv.innerHTML = chamado.detalhamento_resolucao;
    content.appendChild(detalhamentoResolucaoDiv);

    if (profissional) {
        const profissionalWrapper = document.createElement('div');
        profissionalWrapper.classList.add('chamado-profissional');
        profissionalWrapper.innerHTML = `<h5>Profissional</h5><p>${profissional}</p>`;
        content.appendChild(profissionalWrapper);
    }

    if (currEtapa) {
        const etapaWrapper = document.createElement('div');
        etapaWrapper.classList.add('chamado-etapa');
        etapaWrapper.innerHTML = `<h5>Etapa</h5><p>${currEtapa.name}</p>`;
        content.appendChild(etapaWrapper);
    }

    const pontoFocalWrapper = document.createElement('div');
    pontoFocalWrapper.classList.add('chamado-ponto-focal');

    const pontoFocalTitle = document.createElement('h5');
    pontoFocalTitle.innerHTML = 'Ponto Focal';
    pontoFocalWrapper.appendChild(pontoFocalTitle);

    const pontoFocalP = document.createElement('p');
    pontoFocalP.innerHTML = `${sucessoCliente}, ${contatoEmergencia}.`;

    pontoFocalWrapper.appendChild(pontoFocalP);
    content.appendChild(pontoFocalWrapper);

    popupContent.appendChild(content);
    popup.appendChild(popupContent);
};

const partner_set_chamado_form = (response, post_id, popup, popupContent) => {

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
                title_cliente_name.textContent = `${cliente.post_title} #${response.chamado.id}`;
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
            if ((key !== 'chamado-detalhamento-solicitacao' && key !== 'chamado-detalhamento-resolucao') && !value) {
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
        partner_save_chamado(post_id, form, popup);
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

            const settings = wpEditorSettings();

            wp.editor.initialize('chamado-detalhamento-solicitacao', settings);
            wp.editor.initialize('chamado-detalhamento-resolucao', settings);

        }
    });
    form.appendChild(select_marcas);

    if (parseInt(chamado_cliente_id) === parseInt(selected_cliente_id)) {
        selected_marca = chamado_marca;
        addChamadoInputs(response, chamado_cliente_id, selected_cliente_id, chamado_marca, selected_marca, form, true);
    }
};

const addChamadoInputs = (response, chamado_cliente_id, selected_cliente_id, chamado_marca, selected_marca, form, load_chamado = false) => {

    const urgencias = response.urgencias;
    const statuses = response.status;
    const users = response.users;
    const etapas = response.etapas;
    // console.log(chamado_marca, selected_marca);

    // Assunto
    const assuntoInput = document.createElement('input');
    assuntoInput.type = 'text';
    assuntoInput.name = 'chamado-assunto';
    assuntoInput.id = 'chamado-assunto';
    assuntoInput.className = 'chamado-input';
    assuntoInput.placeholder = 'Assunto';
    assuntoInput.required = true;

    // Label do Detalhamento de solicitação
    const detalhamentoSolicitacaoLabel = document.createElement('label');
    detalhamentoSolicitacaoLabel.id = 'chamado-detalhamento-solicitacao-label';
    detalhamentoSolicitacaoLabel.htmlFor = 'chamado-detalhamento-solicitacao';
    detalhamentoSolicitacaoLabel.innerText = 'Detalhamento da solicitação';

    // Detalhamento da Solicitação
    const detalhamentoSolicitacaoInput = document.createElement('textarea');
    detalhamentoSolicitacaoInput.name = 'chamado-detalhamento-solicitacao';
    detalhamentoSolicitacaoInput.id = 'chamado-detalhamento-solicitacao';
    detalhamentoSolicitacaoInput.className = 'chamado-textarea';
    detalhamentoSolicitacaoInput.placeholder = 'Detalhamento da Solicitação';
    detalhamentoSolicitacaoInput.rows = '5';
    detalhamentoSolicitacaoLabel.appendChild(detalhamentoSolicitacaoInput);

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

    // Profissional
    const profissionalSelect = document.createElement('select');
    profissionalSelect.id = 'chamado-profissional';
    profissionalSelect.name = 'chamado-profissional';
    profissionalSelect.className = 'chamado-select';
    profissionalSelect.required = true;

    for (const k in users) {
        const option = document.createElement('option');
        option.value = parseInt(k) === 0 ? '' : parseInt(k);
        option.textContent = users[k];
        if (option.textContent === 'Selecione o usuário') {
            option.textContent = 'Selecione o profissional';
        }
        profissionalSelect.appendChild(option);
    }

    // Estapa
    const etapaSelect = document.createElement('select');
    etapaSelect.name = 'chamado-etapa';
    etapaSelect.id = 'chamado-etapa';
    etapaSelect.className = 'chamado-select';
    etapaSelect.required = true;

    const etapaDefaultOption = document.createElement('option');
    etapaDefaultOption.value = '';
    etapaDefaultOption.textContent = 'Selecione a etapa';
    etapaSelect.appendChild(etapaDefaultOption);

    for (const etapa of etapas) {
        const option = document.createElement('option');
        option.value = etapa.term_id;
        option.textContent = etapa.name;
        etapaSelect.appendChild(option);
    }

    // Label do Detalhamento de solicitação
    const detalhamentoResolucaoLabel = document.createElement('label');
    detalhamentoResolucaoLabel.id = 'chamado-detalhamento-resolucao-label';
    detalhamentoResolucaoLabel.htmlFor = 'chamado-detalhamento-resolucao';
    detalhamentoResolucaoLabel.innerText = 'Detalhamento da Resolução';

    // Detalhamento da Resolução
    const detalhamentoResolucaoInput = document.createElement('textarea');
    detalhamentoResolucaoInput.name = 'chamado-detalhamento-resolucao';
    detalhamentoResolucaoInput.id = 'chamado-detalhamento-resolucao';
    detalhamentoResolucaoInput.className = 'chamado-textarea';
    detalhamentoResolucaoInput.placeholder = 'Detalhamento da Resolução';
    detalhamentoResolucaoInput.rows = '5';
    detalhamentoResolucaoLabel.appendChild(detalhamentoResolucaoInput);

    // Label Notificação
    const notificacaoLabel = document.createElement('label');
    notificacaoLabel.id = 'chamado-notificacao-label';
    notificacaoLabel.htmlFor = 'chamado-notificacao';
    notificacaoLabel.innerText = 'Notificação';

    // Checkbox Notificação
    const notificacaoCheckbox = document.createElement('input');
    notificacaoCheckbox.type = 'checkbox';
    notificacaoCheckbox.id = 'chamado-notificacao';
    notificacaoCheckbox.name = 'chamado-notificacao';
    notificacaoCheckbox.className = 'chamado-checkbox';
    notificacaoCheckbox.checked = false;
    notificacaoLabel.appendChild(notificacaoCheckbox);

    // Submit button
    const submitButton = document.createElement('button');
    submitButton.type = 'submit';
    submitButton.className = 'chamado-button';
    submitButton.id = 'chamado-button';
    submitButton.textContent = 'Salvar';
    submitButton.addEventListener('click', () => {
        tinyMCE.triggerSave();
    });

    // console.log(completed_fields);
    // se for a primeira vez que estiver carregando o chamado, exibe os dados do response.chamado
    // senão, exibe os dados dos campos
    if (load_chamado && response.chamado) {
        assuntoInput.value = response.chamado.assunto;
        detalhamentoSolicitacaoInput.value = response.chamado.detalhamento_solicitacao;
        dataSolicitacaoInput.value = response.chamado.data_solicitacao;
        dataEntregaInput.value = response.chamado.data_entrega;
        urgenciaSelect.value = response.chamado.urgencia;
        statusSelect.value = response.chamado.status;
        profissionalSelect.value = response.chamado.profissional.id;
        etapaSelect.value = response.chamado.etapa;
        detalhamentoResolucaoInput.value = response.chamado.detalhamento_resolucao;
    } else {
        assuntoInput.value = completed_fields.assunto ? completed_fields.assunto : '';
        detalhamentoSolicitacaoInput.value = completed_fields.detalhamento_solicitacao ? completed_fields.detalhamento_solicitacao : '';
        dataSolicitacaoInput.value = completed_fields.data_solicitacao ? completed_fields.data_solicitacao : '';
        dataEntregaInput.value = completed_fields.data_entrega ? completed_fields.data_entrega : '';
        urgenciaSelect.value = completed_fields.urgencia ? completed_fields.urgencia : '';
        statusSelect.value = completed_fields.status ? completed_fields.status : '';
        profissionalSelect.value = completed_fields.profissional ? completed_fields.profissional : '';
        etapaSelect.value = completed_fields.etapa ? completed_fields.etapa : '';
        detalhamentoResolucaoInput.value = completed_fields.detalhamento_resolucao ? completed_fields.detalhamento_resolucao : '';
    }

    form.appendChild(submitButton);
    form.appendChild(notificacaoLabel);
    form.appendChild(assuntoInput);
    form.appendChild(detalhamentoSolicitacaoLabel);
    form.appendChild(dataSolicitacaoLabel);
    form.appendChild(dataEntregaLabel);
    form.appendChild(urgenciaSelect);
    // form.appendChild(pontoFocalSelect);
    form.appendChild(statusSelect);
    form.appendChild(profissionalSelect);
    form.appendChild(etapaSelect);
    form.appendChild(detalhamentoResolucaoLabel);
};

const removeChamadoInputs = () => {
    tinyMCE.triggerSave();
    const inputs_id = {
        cliente: 'partner-chamado-cliente',
        marca: 'partner-chamado-marca',
        assunto: 'chamado-assunto',
        detalhamento_solicitacao: 'chamado-detalhamento-solicitacao',
        data_solicitacao: 'chamado-data-solicitacao',
        data_entrega: 'chamado-data-entrega',
        urgencia: 'chamado-urgencia',
        status: 'chamado-status',
        profissional: 'chamado-profissional',
        etapa: 'chamado-etapa',
        detalhamento_resolucao: 'chamado-detalhamento-resolucao'
    };
    for (const key in inputs_id) {
        if (typeof (document.getElementById(inputs_id[key])) !== 'undefined' && document.getElementById(inputs_id[key]) !== null) {
            completed_fields[key] = document.getElementById(inputs_id[key]).value;
        }
    }
    const assuntoInput = document.getElementById('chamado-assunto');
    if (typeof (assuntoInput) !== 'undefined' && assuntoInput !== null) {
        assuntoInput.remove();
    }
    const detalhamentoSolicitacaoLabel = document.getElementById('chamado-detalhamento-solicitacao-label');
    if (typeof (detalhamentoSolicitacaoLabel) !== 'undefined' && detalhamentoSolicitacaoLabel !== null) {
        detalhamentoSolicitacaoLabel.remove();
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
    const profissionalSelect = document.getElementById('chamado-profissional');
    if (typeof (profissionalSelect) !== 'undefined' && profissionalSelect !== null) {
        profissionalSelect.remove();
    }
    const etapaSelect = document.getElementById('chamado-etapa');
    if (typeof (etapaSelect) !== 'undefined' && etapaSelect !== null) {
        etapaSelect.remove();
    }
    const detalhamentoResolucaoLabel = document.getElementById('chamado-detalhamento-resolucao-label');
    if (typeof (detalhamentoResolucaoLabel) !== 'undefined' && detalhamentoResolucaoLabel !== null) {
        detalhamentoResolucaoLabel.remove();
    }
    const submitButton = document.getElementById('chamado-button');
    if (typeof (submitButton) !== 'undefined' && submitButton !== null) {
        submitButton.remove();
    }
    const notificacaoLabel = document.getElementById('chamado-notificacao-label');
    if (typeof (notificacaoLabel) !== 'undefined' && notificacaoLabel !== null) {
        notificacaoLabel.remove();
    }
    wp.editor.remove('chamado-detalhamento-solicitacao');
    wp.editor.remove('chamado-detalhamento-resolucao');
};

const partner_save_chamado = (post_id, form, popup) => {
    const formData = new FormData(form);
    const action = 'partner_save_chamado';
    let url = ajax_object.ajax_url;
    formData.append('action', action);
    formData.append('post_id', post_id);
    formData.append('partner_nonce', ajax_object.partner_nonce);

    const options = {
        method: 'POST',
        body: formData
    };
    fetch(url, options)
        .then(response => response.json())
        .then(response => {
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
                }
                return;
            }
            form.remove();
            closePopup();
            // atualiza a tela dos chamados
            changeSortSelect();
            //recarrega a página
            // location.reload();
            // partner_atualiza_grid_list();
        })
        .catch(function (error) {
            console.error(error);
        });

};

function partner_ocultar_entregues(status_entregue_id) {
    if (typeof (status_entregue_id) === 'undefined' || status_entregue_id === null) {
        return;
    }
    const filtroStatus = document.getElementById('filtro-status');
    if (typeof (filtroStatus) === 'undefined' || filtroStatus === null) {
        return;
    }
    const checkboxes = filtroStatus.querySelectorAll('input[type="checkbox"]');
    for (const checkbox of checkboxes) {
        if (parseInt(checkbox.value) !== parseInt(status_entregue_id)) {
            const ele = checkbox.parentNode;
            ele.click();
        }
    }
}

function togglePontoFocalDescription() {
    const pontosFocais = document.querySelectorAll('.partner-ponto-focal-atendimento_description');
    for (let pontoFocal of pontosFocais) {
        const toggleBtn = pontoFocal.nextElementSibling;
        if (typeof (toggleBtn) === 'undefined' && toggleBtn === null) {
            return;
        }
        if (!toggleBtn.classList.contains('toggle-descricao')) {
            return;
        }
        let toggleStatus = toggleBtn.getAttribute('data-toggle-status');
        const togglePanel = pontoFocal;
        toggleBtn.addEventListener('click', (e, toggleStatus) => {
            e.preventDefault();
            toggleStatus = toggleBtn.getAttribute('data-toggle-status');
            // get height from togglePanel
            const togglePanelHeight = togglePanel.scrollHeight;
            if (toggleStatus === 'collapsed') {
                toggleBtn.setAttribute('data-toggle-status', 'expanded');
                togglePanel.setAttribute('data-toggle-status', 'expanded');
                togglePanel.style.maxHeight = togglePanelHeight + 'px';
            } else {
                toggleBtn.setAttribute('data-toggle-status', 'collapsed');
                togglePanel.setAttribute('data-toggle-status', 'collapsed');
                togglePanel.style.maxHeight = 0;
            }
        });
    }
}

function setAHrefToOpenInNewTab() {
    const chamadoDetalhamentoSolicitacaoText = document.querySelector('.chamado-detalhamento-solicitacao-text');
    if (typeof (chamadoDetalhamentoSolicitacaoText) !== 'undefined' && chamadoDetalhamentoSolicitacaoText !== null) {
        const links = chamadoDetalhamentoSolicitacaoText.querySelectorAll('a');
        for (const link of links) {
            link.setAttribute('target', '_blank');
        }
    }

    const chamadoDetalhamentoResolucaoText = document.querySelector('.chamado-detalhamento-resolucao-text');
    if (typeof (chamadoDetalhamentoResolucaoText) !== 'undefined' && chamadoDetalhamentoResolucaoText !== null) {
        const links = chamadoDetalhamentoResolucaoText.querySelectorAll('a');
        for (const link of links) {
            link.setAttribute('target', '_blank');
        }
    }
}

document.addEventListener('DOMContentLoaded', function () {
    popupCronogramaInit();
    popupChamadosInit();
    togglePontoFocalDescription();
});
