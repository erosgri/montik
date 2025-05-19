<!-- parte do AJAX -->

<script>
        function aplicarCupom() {
            const codigo = document.getElementById('codigo_cupom').value.trim();
            const mensagemDiv = document.getElementById('mensagem-cupom');

            if (codigo === '') {
                mensagemDiv.innerHTML = '<div class="alert alert-warning">Informe um código de cupom.</div>';
                return;
            }

            fetch('finalizar_compra.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'codigo=' + encodeURIComponent(codigo),
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'ok') {
                        mensagemDiv.innerHTML = `<div class="alert alert-success">${data.mensagem}</div>`;
                        atualizarDescontoETotal(data.valor);
                    } else {
                        mensagemDiv.innerHTML = `<div class="alert alert-danger">${data.mensagem}</div>`;
                        // Remove desconto exibido se erro
                        removerDesconto();
                    }
                })
                .catch(() => {
                    mensagemDiv.innerHTML = '<div class="alert alert-danger">Erro ao verificar o cupom.</div>';
                    removerDesconto();
                });
        }

        function atualizarDescontoETotal(valorDesconto) {
            const descontoLinha = document.getElementById('linha-desconto');
            const valorDescontoTd = document.getElementById('valor-desconto');
            const subtotalTd = document.getElementById('subtotal');
            const freteTd = document.getElementById('frete');
            const totalTd = document.getElementById('total');


            // Pega valores atuais da tabela, converte para número
            let subtotal = parseFloat(subtotalTd.innerText.replace(/[R$\s.]/g, '').replace(',', '.'));
            let frete = parseFloat(freteTd.innerText.replace(/[R$\s.]/g, '').replace(',', '.'));

            if (isNaN(subtotal)) subtotal = 0;
            if (isNaN(frete)) frete = 0;

            // Mostra linha desconto se estiver oculta
            descontoLinha.style.display = '';

            // Atualiza o texto do desconto
            valorDescontoTd.innerText = '- R$ ' + valorDesconto.toFixed(2).replace('.', ',');

            // Atualiza total
            const novoTotal = subtotal - valorDesconto + frete;
            totalTd.innerText = 'R$ ' + novoTotal.toFixed(2).replace('.', ',');
        }

        function removerDesconto() {
            const descontoLinha = document.getElementById('linha-desconto');
            descontoLinha.style.display = 'none';

            const valorDescontoTd = document.getElementById('valor-desconto');
            valorDescontoTd.innerText = '';

            // Atualiza total sem desconto (recarrega a página pode ser melhor)
            window.location.reload();
        }
    </script>