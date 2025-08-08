
# 📊 Solicitação de Gráficos na Dashboard do Filament

## Objetivo:
Incluir gráficos visuais na **Dashboard padrão do Filament**, para exibir dados consolidados de vendas.

Esses gráficos ajudarão na visualização rápida da performance e movimentações recentes.

---

## 📍 Local
- A dashboard padrão exibida ao fazer login no sistema.
- Pode substituir os cards padrões do Filament.

---

## 📈 Gráficos Solicitados

### 1. Vendas nos Últimos 7 Dias (Gráfico de Linhas)
- Eixo X: Dias (data)
- Eixo Y: Valor total vendido por dia
- Exibir uma linha contínua com os totais por dia

### 2. Produtos Mais Vendidos (Gráfico de Barras)
- Exibir os 5 produtos mais vendidos no mês atual
- Eixo X: Nome do Produto
- Eixo Y: Quantidade total vendida

### 3. Total de Vendas por Mês (Gráfico de Barras ou Colunas)
- Eixo X: Mês (últimos 6 meses)
- Eixo Y: Valor total vendido

---

## 🔧 Requisitos Técnicos

- Utilizar `Filament Widgets` para adicionar os gráficos na dashboard.
- Pode usar pacotes como `Filament Charts` ou `Chart.js` integrado via widget.
- Os dados devem ser coletados a partir da tabela de pedidos e itens de venda (módulo já implementado ou em desenvolvimento).
- O layout dos gráficos deve manter a harmonia com a interface padrão do Filament.

---

## 🧩 Extras (opcional)
- Exibir o total de vendas do mês atual em um card acima dos gráficos.
- Mostrar a variação percentual em relação ao mês anterior.

---

📎 **Observação**: Os dados podem ser mockados ou estimados se o módulo de pedidos ainda não estiver com dados reais.
