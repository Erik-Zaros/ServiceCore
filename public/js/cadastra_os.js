$(document).ready(function () {
  const urlParams = new URLSearchParams(window.location.search);
  const osParam = urlParams.get("os");

    if (osParam) {
      carregarDadosOS(osParam);
    } else {
      carregarProdutos();
    }

  function carregarDadosOS(os) {
    $.ajax({
      url: "../public/cadastra_os/buscar.php",
      method: "GET",
      data: { os: os },
      dataType: "json",
      success: function (data) {
        if (data.error) {
          Swal.fire({
            icon: "error",
            title: "Erro",
            text: "Erro ao carregar OS: " + data.error,
          });
          return;
        }

        $("#os").val(data.os);
        $("#data_abertura").val(data.data_abertura);
        $("#nome_consumidor").val(data.nome_consumidor);
        $("#cpf_consumidor").val(data.cpf_consumidor);
        $("#cep_consumidor").val(data.cep_consumidor);
        $("#endereco_consumidor").val(data.endereco_consumidor);
        $("#bairro_consumidor").val(data.bairro_consumidor);
        $("#numero_consumidor").val(data.numero_consumidor);
        $("#cidade_consumidor").val(data.cidade_consumidor);
        $("#estado_consumidor").val(data.estado_consumidor);
        $("#nota_fiscal").val(data.nota_fiscal);

        if (data.nome_tecnico.length > 0) {
          $("#tecnico").val(data.nome_tecnico);
          $("#tecnico").prop('disabled', true);
          $('#campo_tecnico').show();
        }

        carregarProdutos(data.produto);
        carregarPecas(os);
      }
    });
  }

  function carregarProdutos(produtoSelecionado = null) {
    $.ajax({
      url: "../public/produto/listar.php",
      method: "GET",
      cache: false,
      success: function (data) {
        try {
          var produtos = JSON.parse(data);
          $("#produto").empty();
          $("#produto").append('<option value="">Selecione o Produto</option>');

          produtos.forEach(function (produto) {
            if (produto.ativo == "t") {
              var selected =
                produtoSelecionado && produto.produto == produtoSelecionado
                  ? "selected"
                  : "";
              $("#produto").append(
                `<option value="${produto.produto}" ${selected}>${produto.codigo} - ${produto.descricao}</option>`
              );
            }
          });

          $("#produto").select2({
            theme: "bootstrap4",
            width: "100%",
            placeholder: "Selecione o Produto",
            allowClear: true,
          });
        } catch (e) {
          Swal.fire({
            icon: "error",
            title: "Erro",
            text: "Erro ao processar os dados dos produtos.",
          });
        }
      }
    });
  }

  function carregarPecas(os) {
    $.ajax({
      url: "../public/os_item/listar.php",
      method: "GET",
      data: { os: os },
      dataType: "json",
      success: function (pecas) {
        if (!Array.isArray(pecas)) return;
        $("#tabelaPecas tbody").empty();

        pecas.forEach(item => {
          const novaLinha = `
            <tr data-id="${item.peca}">
              <td>${item.codigo}</td>
              <td>${item.descricao}</td>
              <td>${item.quantidade}</td>
              <td>
                <button type="button" class="btn btn-danger btn-sm removerPeca">
                  <i class="bi bi-trash" style="color: white;"></i>
                </button>
              </td>
            </tr>
          `;
          $("#tabelaPecas tbody").append(novaLinha);
        });
      }
    });
  }

  function montarDadosOS() {
    const pecas = [];
    $("#tabelaPecas tbody tr").each(function () {
      pecas.push({
        peca: $(this).data("id"),
        quantidade: $(this).find("td:nth-child(3)").text(),
      });
    });

    return $("#osForm").serialize() + "&pecas=" + encodeURIComponent(JSON.stringify(pecas));
  }

  function gravaEditaOs() {
    const isEdicao = !!$("#os").val();
    const url = isEdicao
      ? "../public/cadastra_os/editar.php"
      : "../public/cadastra_os/cadastrar.php";

    $.ajax({
      url: url,
      method: "POST",
      data: montarDadosOS(),
      dataType: "json",
      success: function (response) {
        if (response.status === "success") {
          const numeroOs = response.os;
          Swal.fire({
            icon: "success",
            title: "Sucesso!",
            text: response.message,
            confirmButtonText: "Visualizar OS",
            showCancelButton: true,
            cancelButtonText: "Fechar",
          }).then((result) => {
            if (result.isConfirmed && numeroOs) {
              window.location.href = `../view/os_press?os=${numeroOs}`;
            } else {
              $("#osForm")[0].reset();
              carregarProdutos();
              $("#tabelaPecas tbody").empty();
            }
          });
        } else {
          Swal.fire({
            icon: "error",
            title: "Erro",
            text: response.message,
          });
        }
      }
    });
  }

  $("#osForm").on("submit", function (e) {
    e.preventDefault();
    gravaEditaOs();
  });

  $("#nome_consumidor").autocomplete({
    minLength: 3,
    source: function (request, response) {
      $.ajax({
        url: "../public/cliente/autocomplete.php",
        dataType: "json",
        data: { term: request.term },
        success: function (data) {
          response(data);
        },
      });
    },
    select: function (event, ui) {
      $("#nome_consumidor").val(ui.item.value);
      $("#cpf_consumidor").val(ui.item.cpf);
      $("#cep_consumidor").val(ui.item.cep);
      $("#endereco_consumidor").val(ui.item.endereco);
      $("#numero_consumidor").val(ui.item.numero);
      $("#bairro_consumidor").val(ui.item.bairro);
      $("#cidade_consumidor").val(ui.item.cidade);
      $("#estado_consumidor").val(ui.item.estado);
      return false;
    },
  });

  let pecaSelecionada = null;

  $("#busca_peca").autocomplete({
    minLength: 2,
    source: function (request, response) {
      const produtoSelecionado = $("#produto").val();

      if (!produtoSelecionado) {
        Swal.fire({
          icon: "warning",
          title: "Selecione um produto primeiro!",
          text: "Você precisa escolher um produto antes de buscar peças.",
        });
        response([]);
        return;
      }

      $.ajax({
        url: "../public/os_item/buscar_pecas.php",
        dataType: "json",
        data: { term: request.term, produto: produtoSelecionado },
        success: function (data) {
          response(data);
        },
        error: function () {
          response([]);
        },
      });
    },
    select: function (event, ui) {
      pecaSelecionada = ui.item.id;
      $("#busca_peca").val(ui.item.label);
      return false;
    },
  });

  $("#btnAdicionarPeca").on("click", function () {
    const qtd = parseInt($("#quantidade_peca").val(), 10);
    const nomePeca = $("#busca_peca").val();

    if (!pecaSelecionada) {
      Swal.fire("Selecione uma peça válida!", "", "warning");
      return;
    }

    if (isNaN(qtd) || qtd <= 0) {
      Swal.fire("Informe uma quantidade válida!", "", "warning");
      return;
    }

    if ($("#tabelaPecas tbody tr[data-id='" + pecaSelecionada + "']").length > 0) {
      Swal.fire("Essa peça já foi adicionada!", "", "info");
      return;
    }

    const [codigo, descricao] = nomePeca.split(" - ");
    const novaLinha = `
      <tr data-id="${pecaSelecionada}">
        <td>${codigo}</td>
        <td>${descricao}</td>
        <td>${qtd}</td>
        <td>
          <button type="button" class="btn btn-danger btn-sm removerPeca">
            <i class="bi bi-trash" style="color: white;"></i>
          </button>
        </td>
      </tr>
    `;

    $("#tabelaPecas tbody").append(novaLinha);

    $("#busca_peca").val("");
    $("#quantidade_peca").val(1);
    pecaSelecionada = null;
  });

  $(document).on("click", ".removerPeca", function () {
    $(this).closest("tr").remove();
  });

  $('#cep_consumidor').on('blur', function () {
      const cep = $('#cep_consumidor').val().replace('-', '');
      if (cep.length === 8) {
          $.ajax({
              url: '../public/cep/buscar.php',
              method: 'POST',
              data: { cep: cep },
              success: function (data) {
                  const endereco = JSON.parse(data);
                  if (!endereco.erro) {
                      $('#endereco_consumidor').val(endereco.logradouro || '');
                      $('#bairro_consumidor').val(endereco.bairro || '');
                      $('#cidade_consumidor').val(endereco.localidade || '');
                      $('#estado_consumidor').val(endereco.uf || '');
                  } else {
                      Swal.fire({
                          icon: 'error',
                          title: 'Erro!',
                          text: 'CEP inválido!',
                      });
                  }
              },
              error: function (xhr, status, error) {
                  Swal.fire({
                      icon: 'error',
                      title: 'Erro!',
                      text: 'Erro ao buscar CEP.',
                  });
              }
          });
      } else {
          Swal.fire({
              icon: 'error',
              title: 'Erro!',
              text: 'CEP deve ter 8 dígitos.',
          });
      }
  });
});
