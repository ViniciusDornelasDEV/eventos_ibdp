  function alerta(url, mensagem){
    if(mensagem == 'desativar'){
        mensagem = "Tem certeza que deseja desativar?";
    }else{
        if(mensagem == 'deletar'){
            mensagem = "Tem certeza que deseja deletar?";
        }else{
            if(mensagem == 'desvincular'){
                mensagem = "Tem certeza que deseja desvincular?";
            }
        }
    }
    bootbox.confirm(mensagem, function(result) {
          if(result){
            location.href = url;
          }
        }); 
  }

  function formEmpresa(){
    $.mask.definitions['~'] = "[+-]";
    $("#cnpj").mask("99.999.999/9999-99");
  }

  //BUSCAR A DESCRIÇÃO DE UM RECURSO
  function BuscaDescricaoRecurso(recurso){
      $('#recurso').attr('disabled', 'disabled');
      var data = {recurso: recurso};
      $.ajax({
          type: "POST",
          url: "/descricaorecurso",
          data: data,
          success: function(html) {
              $('#descricao_recurso').html(html);
              $('#recurso').removeAttr('disabled');       
          }
      });
  }

  function CarregaCidade(estado, required, idCidade = '#cidade'){
    $(idCidade).attr('disabled', 'disabled');
    var data = {estado: estado, required: required};
    $.ajax({
          type: "POST",
          url: "/cidade",
          data: data,
          success: function(html) {
              $(idCidade).html(html);
              $(idCidade).removeAttr('disabled');       
          }
    });
  }

  function carregarQuestionarios(empresa){
    $('#empresa').attr('disabled', 'disabled');
    var data = {empresa: empresa};
    $.ajax({
          type: "POST",
          url: "/associados/grafico/pesquisar/questionarios",
          data: data,
          success: function(html) {
              $('#questionario').html(html);
              $('#empresa').removeAttr('disabled');       
          }
    }); 
  }

  function carregarQuestoes(questionario){
    $('#questionario').attr('disabled', 'disabled');
    var data = {questionario: questionario};
    $.ajax({
          type: "POST",
          url: "/associados/grafico/pesquisar/questoes",
          data: data,
          success: function(html) {
              $('#questao').html(html);
              $('#questionario').removeAttr('disabled');       
          }
    }); 
  }

  function formEvento(){
    $.mask.definitions['~'] = "[+-]";
    mascaraTelefone('#fone_responsavel');
    $("#data_inicio").mask("99/99/9999");
    $("#data_fim").mask("99/99/9999");
    $("#taxa_boleto").mask("9.99");
  }

  function formOpcoes(){
    $.mask.definitions['~'] = "[+-]";
    $("#hora_inicio").mask("99:99");
    $("#hora_fim").mask("99:99");
    
  }

  function formValor(){
    $.mask.definitions['~'] = "[+-]";
    $("#data_inicio_valor").mask("99/99/9999");
    $("#data_fim_valor").mask("99/99/9999");
    $("#valor_inscricao").maskMoney({symbol:'R$ ', showSymbol:true, thousands:'.', decimal:',', symbolStay: true, allowZero: true});
  }

  function formInscricao(){
    $.mask.definitions['~'] = "[+-]";
    $("#cpf").mask("999.999.999-99");
    $("#cnpj").mask("99.999.999/9999-99");
    $("#data_nascimento").mask("99/99/9999");
    
    mascaraTelefone('#telefone_1');
    mascaraTelefone('#telefone_1_2');
    mascaraTelefone('#telefone_2');
    mascaraTelefone('#telefone_2_2');
    mascaraTelefone('#telefone_3');
    mascaraTelefone('#telefone_3_2');
  }

  function mascaraTelefone(campo){
    // jQuery Masked Input
    $(campo).mask("(99) 9999-9999?9").ready(function(event) {
        var target, phone, element;
        target = (event.currentTarget) ? event.currentTarget : event.srcElement;
        phone = target.value.replace(/\D/g, '');
        element = $(target);
        element.unmask();
        if(phone.length > 10) {
            element.mask("(99) 99999-999?9");
        } else {
            element.mask("(99) 9999-9999?9");
        }
    });
     
    $(campo).focusout(function(){
        var phone, element;
        element = $(this);
        element.unmask();
        phone = element.val().replace(/\D/g, '');
        if(phone.length > 10) {
            element.mask("(99) 99999-999?9");
        } else {
            element.mask("(99) 9999-9999?9"); 
        }
    }).trigger('focusout'); 

  }

  function buscaCliente(cpf, idForm){
      var data = {cpf: cpf};
      $("#progresso").show();
      $("#"+idForm+" :input").prop("disabled", true);
      $.ajax({
          type: "POST",
          url: "/cliente/buscar/cpf",
          data: data,
          success: function(json) {
            if(json !== 'false'){
              var cliente = JSON.parse(json); 
              $('#cidade').attr('disabled', 'disabled');
                var data = {estado: cliente.estado, required: false};
                $.ajax({
                      type: "POST",
                      url: "/cidade",
                      data: data,
                      success: function(html) {
                          $('#cidade').html(html);
                          $('#cidade').removeAttr('disabled'); 
                          $('#rg').val(cliente.rg);
                          $('#nome_completo').val(cliente.nome_completo);
                          $('#nome_certificado').val(cliente.nome_certificado);
                          $('#nome_cracha').val(cliente.nome_cracha);
                          $('#telefone').val(cliente.telefone);
                          $('#celular').val(cliente.celular);
                          $('#email').val(cliente.email);
                          $('#data_nascimento').val(cliente.data_nascimento);
                          $('#cep').val(cliente.cep);
                          $('#estado').val(cliente.estado);
                          $('#cidade').val(cliente.cidade);
                          $('#bairro').val(cliente.bairro);
                          $('#nm_rua').val(cliente.nm_rua);
                          $('#numero').val(cliente.numero);
                          $('#complemento').val(cliente.complemento);
                          $('#estado_civil').val(cliente.estado_civil);
                          $('#nacionalidade').val(cliente.nacionalidade);
                          $('#sexo').val(cliente.sexo);
                          $('#conselho').val(cliente.conselho);
                          $('#numero_conselho').val(cliente.numero_conselho);
                          $('#especialidade').val(cliente.especialidade);
                          $('#profissao').val(cliente.profissao);
                          $('#cargo').val(cliente.cargo);      
                      }
                });
            }
          },
          complete:function()
          {
            $('#progresso').hide();
            $("#"+idForm+" :input").prop("disabled", false);
          }
      });
  }


  function carregarCategoriasAssociado(empresa, required, todos = "false"){
    $('#categoria_associado').attr('disabled', 'disabled');
    var data = {empresa: empresa, required: required, todos: todos};
    $.ajax({
          type: "POST",
          url: "/carregar/categoria/associado",
          data: data,
          success: function(html) {
              $('#categoria_associado').html(html);
              $('#categoria_associado').removeAttr('disabled');       
          }
    });
  }

  function carregarAnuidades(categoria, required){
    $('#anuidade').attr('disabled', 'disabled');
    var data = {categoria: categoria, required: required};
    $.ajax({
          type: "POST",
          url: "/associados/pagamentos/carregaranuidade",
          data: data,
          success: function(html) {
              $('#anuidade').html(html);
              $('#anuidade').removeAttr('disabled');       
          }
    });
  }

   function carregarEventos(empresa){
    $('#evento').attr('disabled', 'disabled');
    var data = {empresa: empresa};
    $.ajax({
          type: "POST",
          url: "/carregar/eventos/empresa",
          data: data,
          success: function(html) {
              $('#evento').html(html);
              $('#evento').removeAttr('disabled');       
          }
    });
  }

  function carregarPais(idPais){
    if(idPais == 1){
      //aplicar mascaras
      mascaraTelefone('#telefone');
      mascaraTelefone('#celular');
      $("#cep").mask("99.999-999");

      $("#uf_internacional").hide();
      $("#cidade_internacional").hide();

      $("#estado").show();
      $("#cidade").show();      
    }else{
      $('#telefone').unmask();
      $('#celular').unmask();
      $('#cep').unmask();

      $("#estado").hide();
      $("#cidade").hide();

      $("#uf_internacional").show();
      $("#cidade_internacional").show();
    }
  }