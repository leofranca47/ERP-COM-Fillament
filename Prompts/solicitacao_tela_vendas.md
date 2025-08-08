
# 🧾 Solicitação de Tela de Vendas (Pedidos) - Laravel + Filament

## Contexto:
O sistema já possui cadastro de produtos, grupos, subgrupos, e controle básico de estoque. O próximo passo natural é a criação da tela de **Vendas** (ou Pedidos), que possibilite a geração de pedidos com seus respectivos itens.

## Objetivo:
Implementar um módulo de Vendas/Pedidos utilizando **Filament Resources**, com criação de pedidos, associação de produtos, controle de quantidade, valores, e totalização. A venda deverá afetar o estoque.

Adicionar item **"Vendas"** ao menu principal, após "Produtos".

---

## 🧾 Tela de Pedido de Venda

### Informações do Pedido

| Campo         | Tipo         |
|---------------|--------------|
| Número do Pedido | Automático (UUID ou sequencial) |
| Data da Venda | Data         |
| Cliente       | (pode ser livre ou usar um módulo futuro) |
| Observações   | Texto longo  |
| Status        | Select (Ex: Aberto, Finalizado, Cancelado) |

### Itens da Venda

Cada item terá os campos:

| Campo         | Tipo               |
|---------------|--------------------|
| Produto       | Select (relacional) |
| Quantidade    | Numérico           |
| Valor Unitário| Monetário (autopreenchido pelo produto, editável) |
| Total         | Calculado (Qtd x Valor Unitário) |

Exibir uma **tabela de itens adicionados**, com total geral do pedido ao final.

### Ações esperadas

- Adicionar Item
- Remover Item
- Calcular Total do Pedido
- Botões de **Salvar**, **Voltar**, e opcionalmente **Finalizar Pedido**

---

## 🎯 Requisitos Técnicos

- Utilizar **Filament Forms + Tables**.
- Deve ser possível adicionar múltiplos produtos por pedido.
- Cálculo automático de totais.
- Relacionar pedido com os produtos já cadastrados.
- Ao finalizar um pedido, **opcionalmente decrementar estoque** dos produtos vendidos (pode ser implementado depois).
- Registrar data e status do pedido.
- Associação com cliente (caso haja módulo de clientes).

---

## 🔜 Futuro (não precisa implementar agora)

- Impressão de pedidos ou exportação.
- Geração de nota fiscal (caso módulo fiscal seja implementado).

---

📎 **Observação**: A interface pode seguir o padrão visual do Filament, mantendo consistência com os módulos já criados.
