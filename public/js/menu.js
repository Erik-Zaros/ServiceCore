document.addEventListener("DOMContentLoaded", function () {
    function carregarGraficoPizzaStatusOS(dados) {
        const osAbertas = Number(dados.os_abertas) || 0;
        const osFinalizadas = Number(dados.os_finalizadas) || 0;
        const osCanceladas = Number(dados.os_canceladas) || 0;

        Highcharts.chart('grafico-pizza-os-status', {
            chart: {
                type: 'pie',
                animation: { duration: 700 }
            },
            title: {
                text: 'STATUS DAS ORDENS DE SERVIÇO'
            },
            subtitle: {
                text: 'ABERTAS X FINALIZADAS X CANCELADAS',
                align: 'center',
                style: {
                    fontSize: '16px'
                }
            },
            tooltip: {
                headerFormat: '',
                pointFormat: '<span style="color:{point.color}">\u25cf</span> {point.name}: <b>{point.y}</b> ({point.percentage:.1f}%)'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    borderWidth: 2,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b><br>{point.y} ({point.percentage:.1f}%)',
                        distance: 20
                    }
                }
            },
            series: [{
                enableMouseTracking: false,
                animation: { duration: 1700 },
                colorByPoint: true,
                data: [
                    { name: 'Abertas', y: osAbertas },
                    { name: 'Finalizadas', y: osFinalizadas },
                    { name: 'Canceladas', y: osCanceladas}
                ]
            }],
            colors: ['#ffc107', '#28a745', '#ca2320'],
            accessibility: {
                point: { valueSuffix: '%' }
            }
        });
    }

        (function (H) {
            H.seriesTypes.pie.prototype.animate = function (init) {
                const series = this,
                    chart = series.chart,
                    points = series.points,
                    { animation } = series.options,
                    { startAngleRad } = series;

                function fanAnimate(point, startAngleRad) {
                    const graphic = point.graphic,
                        args = point.shapeArgs;

                    if (graphic && args) {
                        graphic
                            .attr({
                                start: startAngleRad,
                                end: startAngleRad,
                                opacity: 1
                            })
                            .animate({
                                start: args.start,
                                end: args.end
                            }, {
                                duration: animation.duration / points.length
                            }, function () {
                                if (points[point.index + 1]) {
                                    fanAnimate(points[point.index + 1], args.end);
                                }
                                if (point.index === series.points.length - 1) {
                                    series.dataLabelsGroup.animate({
                                        opacity: 1
                                    }, void 0, function () {
                                        points.forEach(point => {
                                            point.opacity = 1;
                                        });
                                        series.update({
                                            enableMouseTracking: true
                                        }, false);
                                        chart.update({
                                            plotOptions: {
                                                pie: {
                                                    innerSize: '40%',
                                                    borderRadius: 8
                                                }
                                            }
                                        });
                                    });
                                }
                            });
                    }
                }

                if (init) {
                    points.forEach(point => {
                        point.opacity = 0;
                    });
                } else {
                    fanAnimate(points[0], startAngleRad);
                }
            };
        }(Highcharts));

        function carregarGraficoColunas(dados) {
            const clientes = Number(dados.clientes) || 0;
            const produtos = Number(dados.produtos) || 0;
            const pecas = Number(dados.pecas) || 0;
            const ordensServico = Number(dados.ordens_servico) || 0;
            const usuarios = Number(dados.usuarios) || 0;

            Highcharts.chart('grafico-colunas', {
                chart: {
                    type: 'column',
                    animation: {
                        duration: 700
                    }
                },
                title: {
                    text: 'Resumo de Registros'
                },
                xAxis: {
                    categories: ['Usuários', 'Clientes', 'Produtos', 'Peças', 'Ordens de Serviço'],
                    title: {
                        text: null
                    }
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: 'Quantidade',
                        align: 'high'
                    }
                },
                tooltip: {
                    valueSuffix: ' registros'
                },
                plotOptions: {
                    column: {
                        dataLabels: {
                            enabled: true
                        }
                    }
                },
                series: [{
                    name: 'Registros',
                    data: [usuarios, clientes, produtos, pecas, ordensServico],
                    color: '#2e2e48'
                }]
            });
        }

        function carregarGraficoPizzaStatusProduto(dados) {

            const produtoInativo = Number(dados.produto_inativo) || 0;
            const produtoAtivo = Number(dados.produto_ativo) || 0;

            if (produtoAtivo === 0 && produtoInativo === 0) {
                console.warn("Nenhum dado disponível para exibir no gráfico.");
                return;
            }

            Highcharts.chart('grafico-pizza-status-produto', {
                chart: {
                    type: 'pie',
                    animation: {
                        duration: 700
                    }
                },
                title: {
                    text: 'PRODUTOS ATIVOS E INATIVOS NO SISTEMA'
                },
                subtitle: {
                    text: 'ATIVOS X INATIVOS',
                    align: 'center',
                    style: {
                        fontSize: '16px'
                    }
                },
                tooltip: {
                    headerFormat: '',
                    pointFormat: '<span style="color:{point.color}">\u25cf</span> {point.name}: <b>{point.y}</b> ({point.percentage:.1f}%)'
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        borderWidth: 2,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b><br>{point.y} ({point.percentage:.1f}%)',
                            distance: 20
                        }
                    }
                },
                series: [{
                    enableMouseTracking: false,
                    animation: {
                        duration: 1700
                    },
                    colorByPoint: true,
                    data: [
                        { name: 'Inativos', y: produtoInativo },
                        { name: 'Ativos', y: produtoAtivo }
                    ]
                }],
                colors: ['#ca2320', '#00ba32'],
                accessibility: {
                    point: {
                        valueSuffix: '%'
                    }
                }
            });
        }

        function carregarGraficoPizzaStatusPeca(dados) {

            const pecaInativa = Number(dados.peca_inativa) || 0;
            const pecaAtiva = Number(dados.peca_ativa) || 0;

            if (pecaAtiva === 0 && pecaInativa === 0) {
                console.warn("Nenhum dado disponível para exibir no gráfico.");
                return;
            }

            Highcharts.chart('grafico-pizza-status-peca', {
                chart: {
                    type: 'pie',
                    animation: {
                        duration: 700
                    }
                },  
                title: {
                    text: 'PEÇAS ATIVAS E INATIVAS NO SISTEMA'
                },
                subtitle: {
                    text: 'ATIVOS X INATIVOS',
                    align: 'center',
                    style: {
                        fontSize: '16px'
                    }
                },
                tooltip: {
                    headerFormat: '',
                    pointFormat: '<span style="color:{point.color}">\u25cf</span> {point.name}: <b>{point.y}</b> ({point.percentage:.1f}%)'
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        borderWidth: 2,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b><br>{point.y} ({point.percentage:.1f}%)',
                            distance: 20
                        }
                    }
                },
                series: [{
                    enableMouseTracking: false,
                    animation: {
                        duration: 1700
                    },
                    colorByPoint: true,
                    data: [
                        { name: 'Inativos', y: pecaInativa },
                        { name: 'Ativos', y: pecaAtiva }
                    ]
                }],
                colors: ['#ca2320', '#00ba32'],
                accessibility: {
                    point: {
                        valueSuffix: '%'
                    }
                }
            });
        }

        $.ajax({
            url: '../public/menu/menu.php',
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                carregarGraficoPizzaStatusOS(response);
                carregarGraficoColunas(response);
                carregarGraficoPizzaStatusProduto(response);
                carregarGraficoPizzaStatusPeca(response);
            }
        });

        const urlParams = new URLSearchParams(window.location.search);
        const alerta = urlParams.get('alerta');

        if (alerta === 'true') {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                text: 'É necessário cadastrar pelo menos um Cliente, Produto e Ordem de Serviço para gerar o arquivo Excel.',
                confirmButtonText: 'OK',
                confirmButtonColor: '#007bff'
            });
        }
    });
