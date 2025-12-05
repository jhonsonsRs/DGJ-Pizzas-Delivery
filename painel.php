<?php
date_default_timezone_set('America/Sao_Paulo');
include 'conecta.php';
if (!isset($_SESSION)) { session_start(); }

if (!isset($_SESSION['usuario_id'])) {
    echo "<script>window.location='login.php';</script>";
    exit;
}

$id = $_SESSION['usuario_id'];
$nome = $_SESSION['usuario_nome'];
$tipo = $_SESSION['tipo_usuario'];
$pagina = isset($_GET['pagina']) ? $_GET['pagina'] : 'home';

$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao'])) {
    
    if ($_POST['acao'] == 'finalizar_pedido') {
        $endereco = pg_escape_string($conexao, $_POST['endereco']);
        $pagamento = pg_escape_string($conexao, $_POST['pagamento']);
        $itens = $_POST['qtd']; 
        $total = 0; $gravar = [];

        foreach ($itens as $pid => $qtd) {
            if($qtd > 0) {
                $res = pg_query($conexao, "SELECT preco_base FROM produtos WHERE id_produto = $pid");
                $prod = pg_fetch_assoc($res);
                $total += $prod['preco_base'] * $qtd;
                $gravar[] = ['id'=>$pid, 'qtd'=>$qtd, 'preco'=>$prod['preco_base']];
            }
        }
        if($total > 0) {
            $data = date('Y-m-d H:i:s');
            $sql = "INSERT INTO pedido (id_cliente, endereco_entrega, forma_pagamento, status_pedido, valor_total, data_hora_pedido, id_cupom) 
                    VALUES ($id, '$endereco', '$pagamento', 'recebido', $total, '$data', 1) RETURNING id_pedido";
            $res = pg_query($conexao, $sql);
            if($res) {
                $row = pg_fetch_row($res);
                $id_novo_pedido = $row[0];
                foreach($gravar as $i) {
                    pg_query($conexao, "INSERT INTO pedido_itens (id_pedido, id_produto, quantidade, preco_unitario) VALUES ($id_novo_pedido, {$i['id']}, {$i['qtd']}, {$i['preco']})");
                }
                $msg = "<div class='form-message success'>Pedido #$id_novo_pedido realizado!</div>";
            } else { $msg = "<div class='form-message error'>Erro: ".pg_last_error($conexao)."</div>"; }
        } else { $msg = "<div class='form-message error'>Selecione pelo menos um item.</div>"; }
    }

    if ($_POST['acao'] == 'atualizar_status') {
        $idp = $_POST['id_pedido']; 
        $st = $_POST['status'];
        $sql = "UPDATE pedido SET status_pedido = '$st', id_atendente = $id WHERE id_pedido = $idp";
        pg_query($conexao, $sql);
        $msg = "<div class='form-message success'>Status do Pedido #$idp atualizado para: ".strtoupper($st)."</div>";
    }

    if ($_POST['acao'] == 'repor') {
        $idi = $_POST['id_insumo']; 
        $qtd = $_POST['qtd'];
        pg_query($conexao, "UPDATE estoque_insumos SET quantidade = quantidade + $qtd WHERE id_insumo = $idi");
        $msg = "<div class='form-message success'>Estoque atualizado!</div>";
    }

    if ($_POST['acao'] == 'pegar_entrega') {
        $idp = $_POST['id_pedido'];
        $sql = "UPDATE pedido SET status_pedido = 'saiu_entrega', id_entregador = $id WHERE id_pedido = $idp";
        pg_query($conexao, $sql);
        $msg = "<div class='form-message success'>Entrega #$idp assumida!</div>";
    }

    if ($_POST['acao'] == 'finalizar_entrega') {
        $idp = $_POST['id_pedido'];
        $sql = "UPDATE pedido SET status_pedido = 'entregue' WHERE id_pedido = $idp";
        pg_query($conexao, $sql);
        $msg = "<div class='form-message success'>Entrega #$idp finalizada!</div>";
    }
}
?>

<?php include 'header.php';?>

<main class="container page-content">
    
    <div class="info-bar" style="margin-top:0; text-align:left; justify-content: space-between;">
        <div>
            <h2>Olá, <?php echo $nome; ?>!</h2>
            <p>Perfil: <strong><?php echo strtoupper($tipo); ?></strong></p>
        </div>
        <div style="align-self:center;">
            <?php echo $msg; ?>
        </div>
    </div>

    <div style="margin: 20px 0; text-align: center;">
        <a href="painel.php?pagina=home" class="btn-secondary">Início</a>
        
        <?php if($tipo == 'cliente'): ?>
            <a href="painel.php?pagina=fazer_pedido" class="btn-primary">Fazer Pedido</a>
            <a href="painel.php?pagina=meus_pedidos" class="btn-secondary">Histórico</a>
        <?php endif; ?>

        <?php if($tipo == 'gerente'): ?>
            <a href="painel.php?pagina=estoque" class="btn-secondary">Estoque</a>
            <a href="painel.php?pagina=relatorios" class="btn-primary">Relatórios</a>
        <?php endif; ?>
        
        <?php if($tipo == 'atendente'): ?>
            <a href="painel.php?pagina=pedidos_novos" class="btn-primary">Pedidos Novos</a>
            <a href="painel.php?pagina=todos_pedidos" class="btn-secondary">Todos os Pedidos</a>
        <?php endif; ?>

        <?php if($tipo == 'entregador'): ?>
            <a href="painel.php?pagina=disponiveis" class="btn-secondary">Entregas Prontas</a>
            <a href="painel.php?pagina=minhas_entregas" class="btn-primary">Minhas Entregas</a>
        <?php endif; ?>
    </div>

    <?php if($pagina == 'home'): ?>
        <h3 style="text-align:center">Cardápio do Dia</h3>
        <div class="menu-grid">
            <?php
            $res = pg_query($conexao, "SELECT * FROM produtos WHERE disponivel = true ORDER BY tipo");
            while($p = pg_fetch_assoc($res)): ?>
            <div class="menu-card">
                <img src="img/<?php echo $p['imagem_url'] ?: 'pizza_padrao.png'; ?>" alt="Foto" onerror="this.src='https://via.placeholder.com/150'">
                <h3><?php echo $p['nome']; ?></h3>
                <p><?php echo $p['descricao']; ?></p>
                <div class="price">R$ <?php echo number_format($p['preco_base'], 2, ',', '.'); ?></div>
            </div>
            <?php endwhile; ?>
        </div>

    <?php elseif($pagina == 'fazer_pedido' && $tipo == 'cliente'): ?>
        <div class="form-container" style="max-width: 800px;">
            <form method="POST" action="painel.php?pagina=meus_pedidos">
                <input type="hidden" name="acao" value="finalizar_pedido">
                <h3>Selecione os Itens:</h3>
                
                <?php
                $res = pg_query($conexao, "SELECT * FROM produtos WHERE disponivel = true ORDER BY tipo");
                while($p = pg_fetch_assoc($res)): ?>
                <div style="display:flex; justify-content:space-between; border-bottom:1px solid #eee; padding:10px 0;">
                    <div><strong><?php echo $p['nome']; ?></strong> (R$ <?php echo $p['preco_base']; ?>)</div>
                    <input type="number" name="qtd[<?php echo $p['id_produto']; ?>]" value="0" min="0" style="width:60px; padding:5px;">
                </div>
                <?php endwhile; ?>

                <div class="form-group" style="margin-top:20px;">
                    <label>Endereço de Entrega:</label>
                    <input type="text" name="endereco" required>
                </div>
                <div class="form-group">
                    <label>Pagamento:</label>
                    <select name="pagamento">
                        <option value="pix">Pix</option>
                        <option value="cartao">Cartão</option>
                        <option value="dinheiro">Dinheiro</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary btn-large">Confirmar Pedido</button>
            </form>
        </div>

    <?php elseif($pagina == 'meus_pedidos' && $tipo == 'cliente'): ?>
        <div class="admin-section">
            <h2>Meus Pedidos</h2>
            <?php
            $res = pg_query($conexao, "SELECT * FROM pedido WHERE id_cliente = $id ORDER BY id_pedido DESC");
            while($p = pg_fetch_assoc($res)): ?>
            <div class="admin-card">
                <p><strong>Pedido #<?php echo $p['id_pedido']; ?></strong> - <?php echo date('d/m/Y H:i', strtotime($p['data_hora_pedido'])); ?></p>
                <p>Total: R$ <?php echo $p['valor_total']; ?> | Status: <span class="status-badge status-<?php echo $p['status_pedido']; ?>"><?php echo strtoupper($p['status_pedido']); ?></span></p>
            </div>
            <?php endwhile; ?>
        </div>
    
    <?php elseif($pagina == 'pedidos_novos' && $tipo == 'atendente'): ?>
        <div class="admin-section">
            <h2>Pedidos Recebidos</h2>
            <p>Pedidos com status 'Recebido' aguardando envio para a cozinha.</p>
            <?php
            $res = pg_query($conexao, "SELECT p.*, c.nome FROM pedido p JOIN cliente c ON p.id_cliente = c.id_cliente WHERE status_pedido = 'recebido' ORDER BY data_hora_pedido ASC");
            if(pg_num_rows($res)==0) echo "<p class='form-message success'>Nenhum pedido novo no momento. Tudo OK!</p>";
            while($p = pg_fetch_assoc($res)): ?>
            <div class="admin-card">
                <h4>#<?php echo $p['id_pedido']; ?> - Cliente: <?php echo $p['nome']; ?></h4>
                <p>Endereço: <?php echo $p['endereco_entrega']; ?></p>
                <form method="POST" style="margin-top:10px;">
                    <input type="hidden" name="acao" value="atualizar_status">
                    <input type="hidden" name="id_pedido" value="<?php echo $p['id_pedido']; ?>">
                    <button name="status" value="em_preparo" class="btn-primary" style="background:#ffb703; color:var(--dark);">Enviar p/ Cozinha</button>
                    <button name="status" value="cancelado" class="btn-secondary" style="background:#dc3545">Cancelar</button>
                </form>
            </div>
            <?php endwhile; ?>
        </div>

    <?php elseif($tipo == 'atendente' && $pagina == 'todos_pedidos'): ?>
        <div class="admin-section">
            <h2>Histórico Geral de Pedidos</h2>
            <p>Lista todos os pedidos em qualquer status (máx. 25).</p>
            <div class="table-responsive">
                <table>
                    <tr><th>#</th><th>Data</th><th>Cliente</th><th>Status</th><th>Total</th></tr>
                    <?php
                    $sql = "SELECT p.*, c.nome as cliente_nome FROM pedido p JOIN cliente c ON p.id_cliente = c.id_cliente ORDER BY p.id_pedido DESC LIMIT 25";
                    $res = pg_query($conexao, $sql);
                    while($p = pg_fetch_assoc($res)):
                        $status_class = str_replace('_', '-', $p['status_pedido']);
                    ?>
                    <tr>
                        <td>#<?php echo $p['id_pedido']; ?></td>
                        <td><?php echo date('d/m H:i', strtotime($p['data_hora_pedido'])); ?></td>
                        <td><?php echo $p['cliente_nome']; ?></td>
                        <td><span class='status-badge status-<?php echo $status_class; ?>'><?php echo strtoupper($p['status_pedido']); ?></span></td>
                        <td>R$ <?php echo number_format($p['valor_total'], 2, ',', '.'); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        </div>

    <?php elseif($pagina == 'estoque' && $tipo == 'gerente'): ?>
        <div class="admin-section">
            <h2>Controle de Estoque</h2>
            <p>Alerta: itens em vermelho estão abaixo do ponto de reposição.</p>
            <?php
            $res = pg_query($conexao, "SELECT * FROM estoque_insumos ORDER BY quantidade ASC");
            while($e = pg_fetch_assoc($res)): 
                $alert = ($e['quantidade'] <= $e['ponto_reposicao']) ? 'style="color:red; font-weight:bold;"' : '';
            ?>
            <div class="admin-card" style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h4 <?php echo $alert; ?>><?php echo $e['nome']; ?></h4>
                    <p>Atual: <?php echo $e['quantidade']." ".$e['unidade_medida']; ?></p>
                </div>
                <form method="POST" class="inline-form">
                    <input type="hidden" name="acao" value="repor">
                    <input type="hidden" name="id_insumo" value="<?php echo $e['id_insumo']; ?>">
                    <input type="number" name="qtd" placeholder="+ Qtd" style="width:70px" required>
                    <button type="submit" class="btn-secondary">Salvar</button>
                </form>
            </div>
            <?php endwhile; ?>
        </div>

    <?php elseif($pagina == 'relatorios' && $tipo == 'gerente'): ?>
        <div class="admin-section">
            <h2>Relatório de Faturamento por Categoria</h2>
            <?php
            $sql_fat = "SELECT p.tipo, COUNT(pi.id_pedido_item) AS qtd_itens, 
                        SUM(pi.quantidade * pi.preco_unitario) AS total_faturado
                        FROM pedido_itens AS pi
                        INNER JOIN produtos AS p ON pi.id_produto = p.id_produto
                        GROUP BY p.tipo
                        ORDER BY total_faturado DESC";
            $res_fat = pg_query($conexao, $sql_fat);

            echo "<table><tr><th>Categoria</th><th>Itens Vendidos</th><th>Faturamento Total</th></tr>";
            while($r = pg_fetch_assoc($res_fat)): ?>
                <tr>
                    <td><?php echo strtoupper($r['tipo']); ?></td>
                    <td><?php echo $r['qtd_itens']; ?></td>
                    <td><strong>R$ <?php echo number_format($r['total_faturado'], 2, ',', '.'); ?></strong></td>
                </tr>
            <?php endwhile; ?>
            </table>
            
            <h2 style="margin-top:2rem;">Top 5 Clientes</h2>
            <?php
            $sql_clientes = "SELECT c.nome, SUM(p.valor_total) AS total_gasto
                            FROM pedido p INNER JOIN cliente c ON p.id_cliente = c.id_cliente
                            GROUP BY c.nome
                            ORDER BY total_gasto DESC LIMIT 5";
            $res_clientes = pg_query($conexao, $sql_clientes);
            echo "<ul>";
            while($r = pg_fetch_assoc($res_clientes)) echo "<li>{$r['nome']} - R$ {$r['total_gasto']}</li>";
            echo "</ul>";
            ?>
        </div>


    <?php elseif($pagina == 'disponiveis' && $tipo == 'entregador'): ?>
        <div class="admin-section">
            <h2>Pedidos Prontos para Entrega</h2>
            <p>Pedidos no status 'Em Preparo' esperando que você os assuma.</p>
            <?php
            $sql = "SELECT p.*, c.nome FROM pedido p JOIN cliente c ON p.id_cliente = c.id_cliente 
                    WHERE p.status_pedido = 'em_preparo' AND p.id_entregador IS NULL";
            $res = pg_query($conexao, $sql);
            if(pg_num_rows($res)==0) echo "<p>Nenhum pedido pronto no momento.</p>";

            while($p = pg_fetch_assoc($res)): ?>
            <div class="admin-card" style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h4>Pedido #<?php echo $p['id_pedido']; ?></h4>
                    <p>Cliente: <?php echo $p['nome']; ?> | Endereço: <strong><?php echo $p['endereco_entrega']; ?></strong></p>
                </div>
                <form method="POST">
                    <input type="hidden" name="acao" value="pegar_entrega">
                    <input type="hidden" name="id_pedido" value="<?php echo $p['id_pedido']; ?>">
                    <button type="submit" class="btn-primary">Assumir Entrega</button>
                </form>
            </div>
            <?php endwhile; ?>
        </div>

    <?php elseif($pagina == 'minhas_entregas' && $tipo == 'entregador'): ?>
        <div class="admin-section">
            <h2>Minhas Entregas em Andamento</h2>
            <p>Pedidos que você já assumiu e precisam ser finalizados.</p>
            <?php
            $sql = "SELECT p.*, c.nome FROM pedido p JOIN cliente c ON p.id_cliente = c.id_cliente 
                    WHERE p.id_entregador = $id AND p.status_pedido = 'saiu_entrega'";
            $res = pg_query($conexao, $sql);
            if(pg_num_rows($res)==0) echo "<p>Você não tem entregas pendentes.</p>";
            
            while($p = pg_fetch_assoc($res)): ?>
            <div class="admin-card" style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h4>Pedido #<?php echo $p['id_pedido']; ?></h4>
                    <p>Cliente: <?php echo $p['nome']; ?> | Endereço: <strong><?php echo $p['endereco_entrega']; ?></strong></p>
                </div>
                <form method="POST">
                    <input type="hidden" name="acao" value="finalizar_entrega">
                    <input type="hidden" name="id_pedido" value="<?php echo $p['id_pedido']; ?>">
                    <button type="submit" class="btn-primary">Finalizar (Entregue)</button>
                </form>
            </div>
            <?php endwhile; ?>
        </div>

    <?php endif; ?>

</main>
<footer><div class="container"><p>&copy; 2025 DGJ Pizzas Delivery</p></div></footer>
</body></html>