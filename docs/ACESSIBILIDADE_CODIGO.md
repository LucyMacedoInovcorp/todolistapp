# 📝 Exemplos de Código - Implementação de Acessibilidade

> **Nota:** Este arquivo contém exemplos de código para referência técnica.  
> Para o status geral de implementação, consulte [ACESSIBILIDADE_MELHORIAS.md](./ACESSIBILIDADE_MELHORIAS.md)

## 🔧 **BotaoUniversal.vue - Componente Acessível**

### Template Vue
```vue
<template>
    <component 
        :is="elementType"
        :type="elementType === 'button' ? type : undefined"
        :class="classes"
        :title="titulo"
        :disabled="disabled"
        :aria-label="ariaLabel || titulo"
        :aria-describedby="ariaDescribedby"
        :tabindex="computedTabIndex"
        @click="handleClick"
        @keydown.enter="handleKeydown"
        @keydown.space="handleKeydown"
    >
        <img 
            v-if="icone" 
            :src="icone" 
            :alt="altIcone" 
            :aria-hidden="apenasIcone ? 'false' : 'true'"
            class="w-3 h-3"
        >
        <slot v-if="!apenasIcone"></slot>
    </component>
</template>
```

### Script de Navegação por Teclado
```javascript
export default {
    props: {
        ariaLabel: { type: String, default: '' },
        ariaDescribedby: { type: String, default: '' }
    },
    computed: {
        computedTabIndex() {
            if (this.disabled) return -1;
            return 0;
        }
    },
    methods: {
        handleKeydown(event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                if (!this.disabled) {
                    this.handleClick(event);
                }
            }
        }
    }
}
```

## 🏗️ **welcome.blade.php - HTML Semântico**

```html
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Aplicação web para gestão de tarefas com design acessível e inclusivo">
    <title>TodoList - Gestão de Tarefas Acessível</title>
</head>
<body>
    <!-- Skip Links -->
    <a href="#main-content" class="skip-link">Ir para o conteúdo principal</a>
    <a href="#navigation" class="skip-link">Ir para a navegação</a>
    
    <div id="app">
        <header role="banner" id="navigation">
            <nav role="navigation" aria-label="Navegação principal"></nav>
        </header>
        <main id="main-content" role="main" tabindex="-1">
            @yield('content')
        </main>
        <footer role="contentinfo"></footer>
    </div>
</body>
</html>
```

## 🎨 **CSS de Acessibilidade**

### Classes Utilitárias
```css
/* Screen readers only */
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

/* Skip links */
.skip-link {
    position: absolute;
    top: -40px;
    left: 6px;
    background: #2563EB;
    color: white;
    padding: 8px;
    text-decoration: none;
    border-radius: 4px;
    z-index: 9999;
    font-weight: 600;
}

.skip-link:focus {
    top: 6px;
}
```

### Focus Indicators
```css
/* Focus visível para navegação por teclado */
button:focus,
input:focus,
select:focus,
a:focus,
.botao-universal:focus,
.botao-icone:focus {
    outline: 2px solid #2563EB !important;
    outline-offset: 2px !important;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.3) !important;
}
```

### Estados de Erro
```css
/* Estados de erro claramente visíveis */
[aria-invalid="true"] {
    border-color: #DC2626 !important;
    box-shadow: 0 0 0 2px rgba(220, 38, 38, 0.2) !important;
}

.error-message {
    color: #DC2626;
    background: #FEF2F2;
    border: 1px solid #FECACA;
    padding: 0.5rem;
    border-radius: 4px;
    font-weight: 500;
}
```

## 📋 **ListComponent.vue - Formulários Acessíveis**

### Estrutura do Formulário
```vue
<form @submit.prevent="adicionarTarefa" 
      role="form" 
      aria-labelledby="form-title"
      aria-describedby="form-description">
    
    <fieldset class="form-fieldset">
        <legend id="form-title" class="form-legend">
            Adicionar Nova Tarefa
        </legend>
        <p id="form-description" class="sr-only">
            Preencha os campos abaixo para criar uma nova tarefa. O título é obrigatório.
        </p>
        
        <div class="form-group">
            <label for="task-title" class="form-label">
                Título da Tarefa
                <span class="required-indicator" aria-label="obrigatório">*</span>
            </label>
            <input 
                id="task-title"
                type="text" 
                v-model="novaTarefa.titulo" 
                required
                aria-required="true"
                :aria-invalid="tituloError ? 'true' : 'false'"
                :aria-describedby="tituloError ? 'title-error' : null"
            />
            <div v-if="tituloError" 
                 id="title-error" 
                 role="alert" 
                 class="error-message">
                {{ tituloError }}
            </div>
        </div>
    </fieldset>
</form>
```

### Lista Semântica
```vue
<section role="region" 
         aria-labelledby="tasks-heading"
         aria-describedby="tasks-summary">
    
    <h2 id="tasks-heading" class="sr-only">Lista de Tarefas</h2>
    <p id="tasks-summary" class="sr-only">
        {{ tarefasFiltradas.length }} tarefa(s) no total
    </p>
    
    <ul role="list" class="task-list" aria-labelledby="tasks-heading">
        <li v-for="tarefa in tarefasFiltradas" 
            :key="tarefa.id" 
            role="listitem"
            :aria-labelledby="`task-title-${tarefa.id}`"
            :aria-describedby="[
                tarefa.descricao ? `task-description-${tarefa.id}` : null,
                (tarefa.data_vencimento || tarefa.prioridade) ? `task-meta-${tarefa.id}` : null
            ].filter(Boolean).join(' ') || undefined">
            
            <h3 :id="`task-title-${tarefa.id}`">{{ tarefa.titulo }}</h3>
            <p :id="`task-description-${tarefa.id}`" v-if="tarefa.descricao">
                {{ tarefa.descricao }}
            </p>
            <div :id="`task-meta-${tarefa.id}`" v-if="tarefa.data_vencimento || tarefa.prioridade">
                <span v-if="tarefa.data_vencimento">📅 {{ formatarData(tarefa.data_vencimento) }}</span>
                <span v-if="tarefa.prioridade">{{ formatarPrioridade(tarefa.prioridade) }}</span>
            </div>
        </li>
    </ul>
</section>
```

## 🧪 **Testes de Acessibilidade**

### Comando para Lighthouse CLI
```bash
# Instalar Lighthouse CLI
npm install -g @lighthouse/cli

# Executar auditoria de acessibilidade
lighthouse http://localhost:8000 --only-categories=accessibility --output=json --output-path=./accessibility-report.json
```

### Verificação Manual
```javascript
// Console do navegador - verificar elementos focáveis
document.querySelectorAll('[tabindex]:not([tabindex="-1"]), a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled])').length

// Verificar elementos com ARIA
document.querySelectorAll('[aria-label], [aria-labelledby], [aria-describedby], [role]').length

// Verificar contraste
// Use a extensão axe DevTools ou WAVE
```

---

**📚 Documentação de Referência:**
- [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)
- [MDN ARIA](https://developer.mozilla.org/en-US/docs/Web/Accessibility/ARIA)
- [Vue.js Accessibility](https://vuejs.org/guide/best-practices/accessibility.html)