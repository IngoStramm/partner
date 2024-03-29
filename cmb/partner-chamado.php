<?php

add_action('cmb2_admin_init', 'partner_register_chamado_metabox');

function partner_register_chamado_metabox()
{
    $cmb_chamado = new_cmb2_box(array(
        'id'            => 'partner_chamado_metabox',
        'title'         => esc_html__('Opções', 'cmb2'),
        'object_types'  => array('chamados'), // Post type
        'context'    => 'normal',
        'priority'   => 'high',
        'cmb_styles' => true,
    ));

    $cmb_chamado->add_field(array(
        'name'    => esc_html__('Cliente', 'partner'),
        'desc'    => esc_html__('Selecione qual é o cliente.', 'partner'),
        'id'      => 'chamado_post',
        'type'    => 'select',
        'options_cb' => function () {
            $options_array = [];
            $options_array[] = __('Selecione uma opção', 'partner');
            $clientes = partner_list_post_clientes_with_id();
            foreach ($clientes as $cliente_id => $cliente_name) {
                $options_array[$cliente_id] = $cliente_name;
            }
            return $options_array;
        },
        'attributes' => array(
            'required' => 'required',
        ),
    ));

    $cmb_chamado->add_field(array(
        'name'    => esc_html__('Marca', 'partner'),
        'desc'    => esc_html__('Selecione qual é a Marca.', 'partner'),
        'id'      => 'chamado_marca_select',
        'type'    => 'select',
        'options' => array('' => __('Nenhum cliente selecionado', 'partner')),
        'attributes' => array(
            'required' => 'required',
        ),
    ));

    $cmb_chamado->add_field(array(
        'name'    => esc_html__('Marca Hidden Input', 'partner'),
        // 'desc'    => esc_html__('Selecione qual é a Marca.', 'partner'),
        'id'      => 'chamado_marca',
        'type'    => 'hidden',
    ));

    $cmb_chamado->add_field(array(
        'name'    => esc_html__('Assunto', 'partner'),
        'desc'    => esc_html__('Descreva o assunto do chamado.', 'partner'),
        'id'      => 'chamado_assunto',
        'type'    => 'text',
        'attributes' => array(
            'required' => 'required',
        ),
    ));

    $cmb_chamado->add_field(array(
        'name'    => esc_html__('Detalhamento', 'partner'),
        'desc'    => esc_html__('Detalhes sobre a solicitação.', 'partner'),
        'id'      => 'chamado_detalhes_solicitacao',
        'type'    => 'wysiwyg',
        'attributes' => array(
            'required' => 'required',
        ),
    ));

    $cmb_chamado->add_field(array(
        'name'    => esc_html__('Data da solicitação', 'partner'),
        // 'desc'    => esc_html__('', 'partner'),
        'id'      => 'chamado_solicitacao',
        'type' => 'text_datetime_timestamp',
        'date_format' => 'd-m-Y',
        'attributes' => array(
            'required' => 'required',
            'autocomplete' => 'off',
        ),
    ));

    $cmb_chamado->add_field(array(
        'name'    => esc_html__('Previsão de entrega', 'partner'),
        // 'desc'    => esc_html__('', 'partner'),
        'id'      => 'chamado_entrega',
        'type' => 'text_datetime_timestamp',
        'date_format' => 'd-m-Y',
        'attributes' => array(
            'required' => 'required',
            'autocomplete' => 'off',
        ),
    ));

    $cmb_chamado->add_field(array(
        'name'    => esc_html__('Urgência', 'partner'),
        // 'desc'    => esc_html__('', 'partner'),
        'id'      => 'chamado_urgencia',
        'type' => 'select',
        'options_cb' => function () {
            $options = [];
            $options[0] = esc_html__('Selecione uma opção', 'partner');
            $statuses = partner_get_urgencia_list();
            foreach ($statuses as $id => $status) {
                $options[$id] = $status;
            }
            return $options;
        },
        'attributes' => array(
            'required' => 'required',
        ),
    ));

    $cmb_chamado->add_field(array(
        'name'    => esc_html__('Status', 'partner'),
        // 'desc'    => esc_html__('', 'partner'),
        'id'      => 'chamado_status',
        'type' => 'select',
        'options_cb' => function () {
            $options = [];
            $options[0] = esc_html__('Selecione uma opção', 'partner');
            $statuses = partner_get_status_list();
            foreach ($statuses as $id => $status) {
                $options[$id] = $status;
            }
            return $options;
        },
        'attributes' => array(
            'required' => 'required',
        ),
    ));

    $cmb_chamado->add_field(array(
        'name'    => esc_html__('Profissional', 'partner'),
        // 'desc'    => esc_html__('', 'partner'),
        'id'      => 'chamado_profissional',
        'type' => 'select',
        'options_cb' => 'partner_list_editor_users',
        'attributes' => array(
            'required' => 'required',
        ),
    ));

    $cmb_chamado->add_field(array(
        'name'    => esc_html__('Etapa', 'partner'),
        // 'desc'    => esc_html__('', 'partner'),
        'id'      => 'chamado_etapa',
        'type' => 'select',
        'options_cb' => function () {
            $options = [];
            $options[0] = esc_html__('Selecione uma opção', 'partner');
            $etapas = partner_get_etapa_list();
            foreach ($etapas as $id => $etapa) {
                $options[$id] = $etapa;
            }
            return $options;
        },
        'attributes' => array(
            'required' => 'required',
        ),
    ));

    $cmb_chamado->add_field(array(
        'name'    => esc_html__('Detalhe a resolução', 'partner'),
        'desc'    => esc_html__('Detalhes sobre a resolução.', 'partner'),
        'id'      => 'chamado_detalhes_resolucao',
        'type'    => 'wysiwyg',
        'attributes' => array(
            'required' => 'required',
        ),
    ));
}
