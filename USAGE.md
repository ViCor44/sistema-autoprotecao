# Guia de Utilização - Sistema de Autoproteção

## Introdução

O Sistema de Autoproteção foi desenvolvido para facilitar a gestão de medidas de autoproteção de uma forma clara e organizada.

## Menu Principal

Após fazer login, você verá o menu navegação no topo:
- **Início** - Dashboard com sumário geral
- **Equipamentos** - Gerir inventário de equipamentos
- **Relatórios** - Ver e criar relatórios de inspeção
- **Calendário** - Agendar e acompanhar manutenções

## 1. Dashboard (Início)

O dashboard é a página inicial após login e mostra:

- **Total de Equipamentos**: Número total de equipamentos registados
- **Manutenções Próximas**: Inspeções agendadas para os próximos 7 dias
- **Manutenções Vencidas**: Inspeções que já passaram da data
- **Relatórios Recentes**: Últimos relatórios dos últimos 30 dias

### Ações Rápidas

Utilize os botões no dashboard para:
1. Registar novo equipamento
2. Criar novo relatório
3. Agendar manutenção
4. Ver relatórios pendentes de assinatura

## 2. Gestão de Equipamentos

### Listar Equipamentos

Clique em **Equipamentos** no menu para ver todos os equipamentos registados.

**Filtros disponíveis:**
- Localização
- Tipo de equipamento

### Adicionar Novo Equipamento

1. Clique em "Novo Equipamento"
2. Preencha os dados:
   - **Tipo de Equipamento** (obrigatório): Selecione entre extintores, hidrantes, etc.
   - **Número de Série**: Referência única do equipamento
   - **Localização** (obrigatório): Onde está localizado
   - **Marca e Modelo**: Identificação do fabricante
   - **Datas**: Aquisição, instalação, próxima manutenção
   - **Estado**: Operacional, inservível, ou aguardando reparação
   - **Observações**: Notas adicionais

3. Clique em "Salvar"

### Editar Equipamento

1. Encontre o equipamento na lista
2. Clique em "Ver" ou "Editar"
3. Altere os dados desejados
4. Clique em "Atualizar"

### Eliminar Equipamento

1. Na lista de equipamentos
2. Clique em "Deletar"
3. Confirme a eliminação

⚠️ **Nota**: Eliminar um equipamento o marca como inativo, mantendo o histórico.

## 3. Relatórios

### Visualizar Relatórios

Clique em **Relatórios** no menu para ver todos os relatórios.

**Filtros disponíveis:**
- Data (período)
- Tipo de relatório
- Estado (assinado/pendente)

### Criar Novo Relatório

1. Clique em "Novo Relatório"
2. Preencha os dados:
   - **Equipamento** (obrigatório): Selecione o equipamento inspecionado
   - **Data**: Data da inspeção
   - **Tipo**: Inspeção, manutenção ou reparação
   - **Descrição**: Detalhes encontrados
   - **Condição**: Bom, aceitável, deficiente ou inservível
   - **Próxima Inspeção**: Data agendada para a próxima
   - **Observações**: Notas adicionais

3. Clique em "Salvar"

### Assinar Relatório

Um relatório precisa ser assinado para estar válido:

1. Aceda ao relatório
2. Revise todos os dados
3. Clique em "Assinar"
4. Confirme a assinatura

### Relatórios Pendentes

Clique em "Relatórios Pendentes" (no menu ou dashboard) para ver todos os relatórios que ainda precisam ser assinados.

## 4. Calendário de Manutenção

### Vista de Calendário

1. Clique em **Calendário** no menu
2. Use os botões < e > para navegar entre meses
3. Equipamentos com inspeções agendadas aparecem assinalados

### Listar Agendamentos

1. Clique em **Calendário** → **Listar Agendamentos**
2. Visualize todas as manutenções agendadas
3. Filtre por status (agendado, em progresso, concluído, cancelado)

### Agendar Manutenção

1. Clique em "Agendar Manutenção"
2. Preencha:
   - **Equipamento** (obrigatório): Selecione qual
   - **Data** (obrigatório): Quando será feita
   - **Tipo**: Que tipo de inspeção
   - **Descrição**: Detalhes da manutenção
   - **Prioridade**: Baixa, normal, alta ou urgente
   - **Responsável**: Quem fará a manutenção

3. Clique em "Agendar"

### Dashboard de Manutenção

Para uma visão rápida do estado das manutenções:

1. Clique em **Calendário** → **Dashboard**
2. Visualize:
   - Manutenções próximas (30 dias)
   - Manutenções vencidas
   - Equipamentos com manutenção pendente

## Dicas e Boas Práticas

### ✅ Recomendações

1. **Mantenha o calendário atualizado** - Agende manutenções com antecedência
2. **Revise regularmente** - Aceda ao dashboard diariamente
3. **Assine relatórios promptamente** - Não deixe pendências
4. **Use campos de observação** - Documente qualquer anomalia
5. **Actualize estados** - Mantenha o estado dos equipamentos preciso

### ⚠️ Evite

1. Criar equipamentos duplicados
2. Deixar relatórios sem assinatura
3. Ignorar equipamentos com estado "Inservível"
4. Não documentar problemas encontrados

## Funcionalidades Avançadas

### Busca e Filtros

Utilize os filtros em cada secção para:
- Encontrar equipamentos por localização
- Filtrar relatórios por período
- Visualizar apenas agendamentos urgentes

### Exportação (em desenvolvimento)

Brevemente será possível exportar:
- Relatórios para PDF
- Dados de equipamentos para Excel
- Calendário para iCalendar

## Problema Frequente

**P: Esqueci minha senha**
R: Contacte o administrador do sistema

**P: Como alterar minha senha?**
R: Esta funcionalidade será adicionada em breve

**P: Posso eliminar um equipamento?**
R: Sim, ao "deletar" um equipamento, ele fica apenas marcado como inativo

**P: Como sair?**
R: Clique no seu nome no canto superior direito → "Sair"

## Suporte

Para problemas ou dúvidas:
1. Consulte esta documentação
2. Contacte o administrador do sistema
3. Verifique o dashboard para alertas

---

**Última atualização**: Abril de 2026
