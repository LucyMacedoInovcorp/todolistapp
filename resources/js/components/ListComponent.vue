<template>

<div class="pagina-principal min-h-screen p-8 flex items-center justify-center">
        <div class="todolist-container">
            <h1 class="text-3xl font-bold mb-8 text-center flex items-center justify-center gap-4">
                <!-- Imagem e título principal -->
                <img src="/images/lapistodolist.png" alt="Lápis TodoList" class="w-12 h-12 icon-destaque">
                Lista de Tarefas
            </h1>
            
            <!-- Conteúdo -->
            <div class="w space-y-4">
                <!-- Formulário da lista com acessibilidade -->
                <div class="todolist-wrapper flex items-center justify-center">
                    <form @submit.prevent="adicionarTarefa" 
                          class="todolist-form" 
                          role="form" 
                          aria-labelledby="form-title"
                          aria-describedby="form-description">
                        
                        <!-- Fieldset com legend -->
                        <fieldset class="form-fieldset">
                            <legend id="form-title" class="form-legend">
                                Adicionar Nova Tarefa
                            </legend>
                            <p id="form-description" class="sr-only">
                                Preencha os campos abaixo para criar uma nova tarefa. O título é obrigatório.
                            </p>
                            
                            <div class="form-inputs">
                                <!-- Campo Título (obrigatório) -->
                                <div class="form-group">
                                    <label for="task-title" class="form-label">
                                        Título da Tarefa
                                        <span class="required-indicator" aria-label="obrigatório">*</span>
                                    </label>
                                    <input 
                                        id="task-title"
                                        type="text" 
                                        v-model="novaTarefa.titulo" 
                                        class="todolist-input-titulo"
                                        required
                                        aria-required="true"
                                        :aria-invalid="tituloError ? 'true' : 'false'"
                                        :aria-describedby="tituloError ? 'title-error' : null"
                                        @blur="validarTitulo"
                                        @input="limparErroTitulo"
                                        placeholder="Digite o título da tarefa"
                                    />
                                    <div v-if="tituloError" 
                                         id="title-error" 
                                         role="alert" 
                                         class="error-message">
                                        {{ tituloError }}
                                    </div>
                                </div>

                                <!-- Campo Descrição (opcional) -->
                                <div class="form-group">
                                    <label for="task-description" class="form-label">
                                        Descrição (opcional)
                                    </label>
                                    <textarea 
                                        id="task-description"
                                        v-model="novaTarefa.descricao" 
                                        class="todolist-input-descricao" 
                                        rows="2"
                                        aria-required="false"
                                        placeholder="Digite uma descrição opcional para a tarefa"
                                    ></textarea>
                                </div>

                                <div class="form-row">
                                    <!-- Campo Data de Vencimento -->
                                    <div class="form-field">
                                        <label for="task-due-date" class="form-label">
                                            Data de Vencimento:
                                        </label>
                                        <input 
                                            id="task-due-date"
                                            type="date" 
                                            v-model="novaTarefa.dataVencimento" 
                                            class="todolist-input-date"
                                            aria-required="false"
                                        />
                                    </div>

                                    <!-- Campo Prioridade -->
                                    <div class="form-field">
                                        <label for="task-priority" class="form-label">
                                            Prioridade:
                                        </label>
                                        <select 
                                            id="task-priority"
                                            v-model="novaTarefa.prioridade" 
                                            class="todolist-input-select"
                                            aria-required="false"
                                        >
                                            <option value="baixa">Baixa</option>
                                            <option value="media">Média</option>
                                            <option value="alta">Alta</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Botão de submit -->
                                <BotaoUniversal 
                                    type="submit" 
                                    variante="bottom"
                                    icone="/images/plus.png" 
                                    alt-icone="Adicionar"
                                >
                                    Adicionar Tarefa
                                </BotaoUniversal>
                            </div>
                        </fieldset>
                    </form>
                </div>

                <!-- Filtros -->
                <FiltroTarefas 
                    :filtros="filtros" 
                    @atualizar-filtros="atualizarFiltros"
                />

                <!-- Lista de tarefas -->
                <div class="todolist-wrapper">
                    <div class="todolist-tarefas min-h-20 conteudo-dinamico rounded-lg px-4 py-3">
                    <div v-if="tarefas.length === 0" class="text-center text-gray-500 italic">
                        Nenhuma tarefa adicionada ainda.
                    </div>
                    
                    <!-- Lista semântica de tarefas -->
                    <section 
                        role="region" 
                        aria-labelledby="tasks-heading"
                        aria-describedby="tasks-summary">
                        
                        <h2 id="tasks-heading" class="tasks-section-title sr-only">
                            Lista de Tarefas
                        </h2>
                        <p id="tasks-summary" class="sr-only">
                            {{ tarefasFiltradas.length }} tarefa(s) {{ filtros.estado !== 'todas' ? 'filtrada(s) por ' + filtros.estado : 'no total' }}
                        </p>
                        
                        <ul role="list" class="task-list" aria-labelledby="tasks-heading">
                            <li v-for="tarefa in tarefasFiltradas" 
                                :key="tarefa.id" 
                                role="listitem"
                                :aria-labelledby="`task-title-${tarefa.id}`"
                                :aria-describedby="[
                                    tarefa.descricao ? `task-description-${tarefa.id}` : null,
                                    (tarefa.data_vencimento || tarefa.prioridade) ? `task-meta-${tarefa.id}` : null
                                ].filter(Boolean).join(' ') || undefined"
                                class="todolist-tarefa mb-4 pb-4 border-b border-gray-200 last:border-b-0 last:mb-0 last:pb-0">
                        <!-- Visualização normal -->
                        <div v-if="!tarefa.editando" class="flex items-center justify-between w-full gap-3">
                            <div class="flex items-start gap-3 flex-1">
                                <input 
                                    type="checkbox" 
                                    :checked="tarefa.concluida" 
                                    @change="toggleTarefa(tarefa.id)"
                                    class="progresso mt-1" 
                                />
                                <div class="tarefa-conteudo flex-1">
                                    <h3 
                                        :id="`task-title-${tarefa.id}`"
                                        class="tarefa-titulo cursor-pointer" 
                                        :class="{ 'line-through opacity-60': tarefa.concluida }"
                                        @click="mostrarDetalhes(tarefa)"
                                    >
                                        {{ tarefa.titulo }}
                                    </h3>
                                    <p 
                                        v-if="tarefa.descricao" 
                                        :id="`task-description-${tarefa.id}`"
                                        class="tarefa-descricao cursor-pointer"
                                        :class="{ 'line-through opacity-60': tarefa.concluida }"
                                        @click="mostrarDetalhes(tarefa)"
                                    >
                                        {{ tarefa.descricao }}
                                    </p>
                                    <div v-if="tarefa.data_vencimento || tarefa.prioridade" 
                                         :id="`task-meta-${tarefa.id}`"
                                         class="tarefa-meta">
                                        <span v-if="tarefa.data_vencimento" class="vencimento" :class="{ 'vencida': isVencida(tarefa.data_vencimento), 'vence-hoje': venceHoje(tarefa.data_vencimento) }">
                                            📅 {{ formatarData(tarefa.data_vencimento) }}
                                        </span>
                                        <span v-if="tarefa.prioridade" :class="['prioridade-badge', `prioridade-${tarefa.prioridade}`]">
                                            {{ formatarPrioridade(tarefa.prioridade) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="acoes flex gap-2 ml-auto">
                                <BotaoUniversal 
                                    icone="/images/lapiseditar.png" 
                                    alt-icone="Editar" 
                                    titulo="Editar tarefa"
                                    variante="editar"
                                    apenas-icone
                                    @click="editarTarefa(tarefa)"
                                />
                                <BotaoUniversal 
                                    icone="/images/lapisexcluir.png" 
                                    alt-icone="Excluir" 
                                    titulo="Excluir tarefa"
                                    variante="excluir"
                                    apenas-icone
                                    @click="excluirTarefa(tarefa.id)"
                                />
                            </div>
                        </div>

                        <!-- Formulário de edição -->

                        <div v-if="tarefa.editando" class="editar-tarefa-form flex items-center w-full gap-3">
                            <input 
                                type="checkbox" 
                                :checked="tarefa.concluida" 
                                disabled
                                class="progresso mt-1" 
                            />
                            <div class="form-inputs-edicao flex-1">
                                <input 
                                    type="text" 
                                    v-model="tarefa.tituloTemp" 
                                    class="editar-titulo-input mb-2" 
                                    placeholder="Título da tarefa" 
                                />
                                <textarea 
                                    v-model="tarefa.descricaoTemp" 
                                    class="editar-descricao-input mb-2" 
                                    placeholder="Descrição" 
                                    rows="2"
                                ></textarea>
                                <div class="form-row-edit">
                                    <input type="date" v-model="tarefa.dataVencimentoTemp" class="editar-date-input" />
                                    <select v-model="tarefa.prioridadeTemp" class="editar-select-input">
                                        <option value="baixa">Baixa</option>
                                        <option value="media">Média</option>
                                        <option value="alta">Alta</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex gap-2 ml-auto">
                                <BotaoUniversal 
                                    icone="/images/check.png" 
                                    alt-icone="Confirmar edição"
                                    titulo="Confirmar edição"
                                    variante="success"
                                    apenas-icone
                                    @click="salvarEdicao(tarefa)"
                                />
                                <BotaoUniversal 
                                    icone="/images/excluir.png" 
                                    alt-icone="Cancelar edição"
                                    titulo="Cancelar edição"
                                    variante="excluir"
                                    apenas-icone
                                    @click="cancelarEdicao(tarefa)"
                                />
                            </div>
                        </div>
                    </li>
                        </ul>
                    </section>
                    </div>
                </div>

                <!-- Modal de Detalhes -->
                <DetalheTarefa
                    :tarefa="tarefaSelecionada || {}"
                    :mostrar="mostrarModalDetalhes"
                    @fechar="fecharDetalhes"
                    @editar="editarTarefa"
                    @toggle="toggleTarefa"
                    @excluir="excluirTarefa"
                />

            </div>
        </div>
    </div>
</template>

<script>
import FiltroTarefas from './FiltroTarefas.vue';
import DetalheTarefa from './DetalheTarefa.vue';
import BotaoUniversal from './BotaoUniversal.vue';

export default {
    name: 'ListComponent',
    components: {
        FiltroTarefas,
        DetalheTarefa,
        BotaoUniversal
    },
    data() {
        return {
            tarefas: [],
            novaTarefa: {
                titulo: '',
                descricao: '',
                dataVencimento: '',
                prioridade: 'media'
            },
            tarefaSelecionada: null,
            mostrarModalDetalhes: false,
            
            // Sistema de validação simplificado
            tituloError: '',
            
            filtros: {
            estado: 'todas', 
            prioridade: 'todas', 
            dataVencimento: 'todas' 
            }
        }
    },
    mounted() {
        this.carregarTarefas();
    },
    methods: {
        async carregarTarefas() {
            try {
                const headers = {
                    'Content-Type': 'application/json'
                };
                
                // Adicionar token se disponível
                const token = window.getAuthToken && window.getAuthToken();
                if (token) {
                    headers['Authorization'] = `Bearer ${token}`;
                }
                
                const response = await fetch('/api/tarefas', { headers });
                this.tarefas = await response.json();
            } catch (error) {
                console.error('Erro ao carregar tarefas:', error);
            }
        },

        async adicionarTarefa() {
            // Validação simples
            if (!this.novaTarefa.titulo.trim()) {
                this.tituloError = 'O título da tarefa é obrigatório.';
                document.getElementById('task-title').focus();
                return;
            }

            try {
                const headers = {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                };
                
                // Adicionar token se disponível
                const token = window.getAuthToken && window.getAuthToken();
                if (token) {
                    headers['Authorization'] = `Bearer ${token}`;
                }
                
                const response = await fetch('/api/tarefas', {
                    method: 'POST',
                    headers,
                    body: JSON.stringify({
                        titulo: this.novaTarefa.titulo,
                        descricao: this.novaTarefa.descricao,
                        dataVencimento: this.novaTarefa.dataVencimento,
                        prioridade: this.novaTarefa.prioridade
                    })
                });

                if (response.ok) {
                    const novaTarefa = await response.json();
                    this.tarefas.unshift(novaTarefa);
                    // Reset simples
                    this.novaTarefa = { titulo: '', descricao: '', dataVencimento: '', prioridade: 'media' };
                    this.tituloError = '';
                }
            } catch (error) {
                console.error('Erro ao adicionar tarefa:', error);
            }
        },

        async toggleTarefa(id) {
            try {
                const headers = {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                };
                
                // Adicionar token se disponível
                const token = window.getAuthToken && window.getAuthToken();
                if (token) {
                    headers['Authorization'] = `Bearer ${token}`;
                }
                
                const response = await fetch(`/api/tarefas/${id}/toggle`, {
                    method: 'PATCH',
                    headers
                });

                if (response.ok) {
                    const tarefaAtualizada = await response.json();
                    const index = this.tarefas.findIndex(t => t.id === id);
                    if (index !== -1) {
                        this.tarefas[index] = tarefaAtualizada;
                        
                        // Se a tarefa atualizada é a que está sendo mostrada no modal, atualiza também
                        if (this.tarefaSelecionada && this.tarefaSelecionada.id === id) {
                            this.tarefaSelecionada = tarefaAtualizada;
                        }
                    }
                }
            } catch (error) {
                console.error('Erro ao alterar status da tarefa:', error);
            }
        },

        editarTarefa(tarefa) {
            tarefa.editando = true;
            tarefa.tituloTemp = tarefa.titulo;
            tarefa.descricaoTemp = tarefa.descricao;
            tarefa.dataVencimentoTemp = tarefa.data_vencimento;
            tarefa.prioridadeTemp = tarefa.prioridade || 'media';
        },

        cancelarEdicao(tarefa) {
            tarefa.editando = false;
            delete tarefa.tituloTemp;
            delete tarefa.descricaoTemp;
            delete tarefa.dataVencimentoTemp;
            delete tarefa.prioridadeTemp;
        },

        async salvarEdicao(tarefa) {
            try {
                const headers = {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                };
                
                // Adicionar token se disponível
                const token = window.getAuthToken && window.getAuthToken();
                if (token) {
                    headers['Authorization'] = `Bearer ${token}`;
                }
                
                const response = await fetch(`/api/tarefas/${tarefa.id}`, {
                    method: 'PUT',
                    headers,
                    body: JSON.stringify({
                        titulo: tarefa.tituloTemp,
                        descricao: tarefa.descricaoTemp,
                        dataVencimento: tarefa.dataVencimentoTemp,
                        prioridade: tarefa.prioridadeTemp
                    })
                });

                if (response.ok) {
                    const tarefaAtualizada = await response.json();
                    Object.assign(tarefa, tarefaAtualizada);
                    tarefa.editando = false;
                    delete tarefa.tituloTemp;
                    delete tarefa.descricaoTemp;
                    delete tarefa.dataVencimentoTemp;
                    delete tarefa.prioridadeTemp;
                }
            } catch (error) {
                console.error('Erro ao salvar edição:', error);
            }
        },

        async excluirTarefa(id) {
            if (!confirm('Tem certeza que deseja excluir esta tarefa?')) return;

            try {
                const headers = {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                };
                
                // Adicionar token se disponível
                const token = window.getAuthToken && window.getAuthToken();
                if (token) {
                    headers['Authorization'] = `Bearer ${token}`;
                }
                
                const response = await fetch(`/api/tarefas/${id}`, {
                    method: 'DELETE',
                    headers
                });

                if (response.ok) {
                    this.tarefas = this.tarefas.filter(t => t.id !== id);
                }
            } catch (error) {
                console.error('Erro ao excluir tarefa:', error);
            }
        },

        formatarData(data) {
            if (!data) return '';
            
            let date;
            // Tenta diferentes formatos de data
            if (data.includes('-')) {
                // Formato YYYY-MM-DD (do input date e banco)
                const [ano, mes, dia] = data.split('-');
                date = new Date(parseInt(ano), parseInt(mes) - 1, parseInt(dia));
            } else {
                // Tenta parseamento direto
                date = new Date(data);
            }
            
            // Verifica se a data é válida
            if (isNaN(date.getTime())) {
                return 'Data inválida';
            }
            
            return date.toLocaleDateString('pt-BR');
        },

        isVencida(data) {
            if (!data) return false;
            
            let vencimento;
            if (data.includes('-')) {
                const [ano, mes, dia] = data.split('-');
                vencimento = new Date(parseInt(ano), parseInt(mes) - 1, parseInt(dia));
            } else {
                vencimento = new Date(data);
            }
            
            if (isNaN(vencimento.getTime())) return false;
            
            const hoje = new Date();
            hoje.setHours(0, 0, 0, 0);
            vencimento.setHours(0, 0, 0, 0);
            return vencimento < hoje;
        },

        venceHoje(data) {
            if (!data) return false;
            
            let vencimento;
            if (data.includes('-')) {
                const [ano, mes, dia] = data.split('-');
                vencimento = new Date(parseInt(ano), parseInt(mes) - 1, parseInt(dia));
            } else {
                vencimento = new Date(data);
            }
            
            if (isNaN(vencimento.getTime())) return false;
            
            const hoje = new Date();
            hoje.setHours(0, 0, 0, 0);
            vencimento.setHours(0, 0, 0, 0);
            return vencimento.getTime() === hoje.getTime();
        },

        formatarPrioridade(prioridade) {
            const prioridadeMap = {
                'baixa': 'BAIXA',
                'media': 'MÉDIA',
                'alta': 'ALTA'
            };
            return prioridadeMap[prioridade] || prioridade.toUpperCase();
        },

        atualizarFiltros(evento) {
            if (evento.campo === 'reset') {
                this.filtros = evento.valor;
            } else {
                this.filtros[evento.campo] = evento.valor;
            }
        },

        mostrarDetalhes(tarefa) {
            this.tarefaSelecionada = tarefa;
            this.mostrarModalDetalhes = true;
        },

        fecharDetalhes() {
            this.mostrarModalDetalhes = false;
            this.tarefaSelecionada = null;
        }
    },
    computed: {
    tarefasFiltradas() {
        let resultado = this.tarefas;
        
        // Filtro por estado (pendente/concluída)
        if (this.filtros.estado !== 'todas') {
            if (this.filtros.estado === 'pendentes') {
                resultado = resultado.filter(tarefa => !tarefa.concluida);
            } else if (this.filtros.estado === 'concluidas') {
                resultado = resultado.filter(tarefa => tarefa.concluida);
            }
        }
        
        // Filtro por prioridade
        if (this.filtros.prioridade !== 'todas') {
            resultado = resultado.filter(tarefa => tarefa.prioridade === this.filtros.prioridade);
        }
        
        // Filtro por data de vencimento
        if (this.filtros.dataVencimento !== 'todas') {
            resultado = resultado.filter(tarefa => {
                if (!tarefa.data_vencimento) return false;
                
                if (this.filtros.dataVencimento === 'vencidas') {
                    return this.isVencida(tarefa.data_vencimento);
                } else if (this.filtros.dataVencimento === 'hoje') {
                    return this.venceHoje(tarefa.data_vencimento);
                } else if (this.filtros.dataVencimento === 'futuras') {
                    return !this.isVencida(tarefa.data_vencimento) && !this.venceHoje(tarefa.data_vencimento);
                }
            });
        }
        
        return resultado;
    },

    // Métodos de validação simplificados
    validarTitulo() {
        if (!this.novaTarefa.titulo.trim()) {
            this.tituloError = 'O título da tarefa é obrigatório.';
        } else if (this.novaTarefa.titulo.trim().length < 3) {
            this.tituloError = 'O título deve ter pelo menos 3 caracteres.';
        } else {
            this.tituloError = '';
        }
    },

    limparErroTitulo() {
        if (this.tituloError) {
            this.tituloError = '';
        }
    }
}
}
</script>



