import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  ScrollView,
  TextInput,
  ActivityIndicator,
  RefreshControl,
  TouchableOpacity,
  Alert,
  Modal,
} from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { authService } from '../../services/authService';
import {
  Container,
  Header,
  Avatar,
  AppName,
  SearchBar,
  Section,
  SectionTitle,
  Card,
  Tag,
  Name,
  Address,
  StatusCount,
  SectionHeader
} from './styles';

const STATUS_MAP: any = {
  'ABERTO': { name: 'Aberto', color: '#ffc107' },
  'EM_ANDAMENTO': { name: 'Em Andamento', color: '#17a2b8' },
  'FINALIZADO': { name: 'Finalizado', color: '#28a745' },
  'CANCELADO': { name: 'Cancelado', color: '#dc3545' }
};

export default function Dashboard() {
  const [ordensServico, setOrdensServico] = useState<any>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [searchText, setSearchText] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [concluindoOrdem, setConcluindoOrdem] = useState<number | null>(null);
  const [showProfileModal, setShowProfileModal] = useState(false);
  const [tecnicoData, setTecnicoData] = useState<any>(null);
  const [loadingTecnico, setLoadingTecnico] = useState(false);

  const navigation = useNavigation<any>();

  const buscarOrdens = async (showLoading = true) => {
    try {
      if (showLoading) setLoading(true);
      setError(null);

      const data = await authService.buscarTickets();
      setOrdensServico(data);
    } catch (error) {
      const errorMessage = error instanceof Error ? error.message : 'Erro desconhecido';
      setError(errorMessage);

      if (errorMessage === 'Sessão expirada') {
        Alert.alert(
          'Sessão Expirada',
          'Sua sessão expirou. Faça login novamente.',
          [{
            text: 'OK',
            onPress: () => navigation.navigate('Login')
          }]
        );
      } else {
        Alert.alert('Erro', 'Não foi possível carregar as ordens de serviço');
      }
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const refresh = () => {
    setRefreshing(true);
    buscarOrdens(false);
  };

  const handleAvatarPress = async () => {
    setLoadingTecnico(true);
    setShowProfileModal(true);

    try {
      const userLogin = await authService.getUserLogin();

      if (!userLogin) {
        Alert.alert('Erro', 'Login do usuário não encontrado');
        setShowProfileModal(false);
        return;
      }

      const data = await authService.buscarTecnico(userLogin);
      setTecnicoData(data);

    } catch (error) {
      const errorMessage = error instanceof Error ? error.message : 'Erro ao buscar dados do técnico';
      Alert.alert('Erro', errorMessage);
      setShowProfileModal(false);
    } finally {
      setLoadingTecnico(false);
    }
  };

  const handleLogout = () => {
    Alert.alert(
      'Sair',
      'Tem certeza que deseja sair?',
      [
        { text: 'Cancelar', style: 'cancel' },
        {
          text: 'Sair',
          style: 'destructive',
          onPress: async () => {
            await authService.logout();
            navigation.navigate('Login');
          }
        }
      ]
    );
  };

  const handleExportarTicket = async (ticket: any) => {
    const ticketId = ticket.ticket;

    if (!ticketId) {
      Alert.alert('Erro', 'ID do ticket não encontrado');
      return;
    }

    console.log('Exportando ticket:', ticketId);
    setConcluindoOrdem(ticketId);

    try {
      const response = await authService.marcarTicketComoExportado(ticketId);
      console.log('Resposta da API (exportar):', response);
      Alert.alert('Sucesso', 'Ticket marcado como exportado!');
      buscarOrdens(false);

    } catch (error) {
      const errorMessage = error instanceof Error ? error.message : 'Erro desconhecido';
      console.error('Erro ao exportar ticket:', error);
      Alert.alert('Erro ao exportar', errorMessage);

    } finally {
      setConcluindoOrdem(null);
    }
  };

  const handleFinalizarTicket = async (ticket: any) => {
    const ticketId = ticket.ticket;

    if (!ticketId) {
      Alert.alert('Erro', 'ID do ticket não encontrado');
      return;
    }

    console.log('Finalizando ticket:', ticketId);
    setConcluindoOrdem(ticketId);

    try {
      const response = await authService.atualizarStatusTicket(ticketId, 'finalizado');
      console.log('Resposta da API (finalizar):', response);
      Alert.alert('Sucesso', 'Ticket finalizado com sucesso!');
      buscarOrdens(false);

    } catch (error) {
      const errorMessage = error instanceof Error ? error.message : 'Erro desconhecido';
      console.error('Erro ao finalizar ticket:', error);
      Alert.alert('Erro ao finalizar', errorMessage);

    } finally {
      setConcluindoOrdem(null);
    }
  };

  const renderBotaoAcao = (item: any) => {
    const isLoading = concluindoOrdem === item.ticket;
    const status = item.status || 'ABERTO';
    const exportado = item.exportado || false;

    // Se já está finalizado
    if (status === 'FINALIZADO') {
      return (
        <View style={{
          backgroundColor: '#d1eddb',
          paddingHorizontal: 12,
          paddingVertical: 6,
          borderRadius: 12,
          borderWidth: 1,
          borderColor: '#28a745'
        }}>
          <Text style={{ color: '#155724', fontSize: 11, fontWeight: 'bold' }}>
            ✅ Finalizado
          </Text>
        </View>
      );
    }

    // Se está cancelado
    if (status === 'CANCELADO') {
      return (
        <View style={{
          backgroundColor: '#f8d7da',
          paddingHorizontal: 12,
          paddingVertical: 6,
          borderRadius: 12,
          borderWidth: 1,
          borderColor: '#dc3545'
        }}>
          <Text style={{ color: '#721c24', fontSize: 11, fontWeight: 'bold' }}>
            ❌ Cancelado
          </Text>
        </View>
      );
    }

    // Se ainda NÃO foi exportado - mostrar botão EXPORTAR
    if (!exportado) {
      return (
        <TouchableOpacity
          onPress={() => handleExportarTicket(item)}
          disabled={isLoading}
          style={{
            backgroundColor: isLoading ? '#6c757d' : '#ffc107',
            paddingHorizontal: 16,
            paddingVertical: 8,
            borderRadius: 15,
            flexDirection: 'row',
            alignItems: 'center',
            elevation: 2,
            shadowColor: '#000',
            shadowOffset: { width: 0, height: 2 },
            shadowOpacity: 0.2,
            shadowRadius: 4,
          }}
        >
          {isLoading ? (
            <>
              <ActivityIndicator size="small" color="#fff" style={{ marginRight: 6 }} />
              <Text style={{ color: '#fff', fontSize: 12, fontWeight: 'bold' }}>
                Exportando...
              </Text>
            </>
          ) : (
            <>
              <Text style={{ fontSize: 14, marginRight: 4 }}>📤</Text>
              <Text style={{ color: '#fff', fontSize: 12, fontWeight: 'bold' }}>
                Exportar
              </Text>
            </>
          )}
        </TouchableOpacity>
      );
    }

    // Se JÁ foi exportado - mostrar botão FINALIZAR
    return (
      <TouchableOpacity
        onPress={() => handleFinalizarTicket(item)}
        disabled={isLoading}
        style={{
          backgroundColor: isLoading ? '#6c757d' : '#28a745',
          paddingHorizontal: 16,
          paddingVertical: 8,
          borderRadius: 15,
          flexDirection: 'row',
          alignItems: 'center',
          elevation: 2,
          shadowColor: '#000',
          shadowOffset: { width: 0, height: 2 },
          shadowOpacity: 0.2,
          shadowRadius: 4,
        }}
      >
        {isLoading ? (
          <>
            <ActivityIndicator size="small" color="#fff" style={{ marginRight: 6 }} />
            <Text style={{ color: '#fff', fontSize: 12, fontWeight: 'bold' }}>
              Finalizando...
            </Text>
          </>
        ) : (
          <>
            <Text style={{ fontSize: 14, marginRight: 4 }}>✅</Text>
            <Text style={{ color: '#fff', fontSize: 12, fontWeight: 'bold' }}>
              Finalizar
            </Text>
          </>
        )}
      </TouchableOpacity>
    );
  };

  const agruparPorStatus = () => {
    let ordensFiltradas = ordensServico;

    if (searchText.trim()) {
      const textoBusca = searchText.toLowerCase().trim();
      ordensFiltradas = ordensServico.filter((ticket: any) =>
        ticket.dados?.informacoes_consumidor?.nome_consumidor?.toLowerCase().includes(textoBusca) ||
        ticket.dados?.informacoes_produto?.produto_descricao?.toLowerCase().includes(textoBusca) ||
        ticket.dados?.informacoes_os?.nota_fiscal?.toLowerCase().includes(textoBusca)
      );
    }

    const grupos: any = {};

    ordensFiltradas.forEach((ticket: any) => {
      const status = ticket.status || 'ABERTO';
      if (!grupos[status]) {
        grupos[status] = [];
      }
      grupos[status].push(ticket);
    });

    return Object.keys(grupos).map(status => ({
      status: status,
      statusName: STATUS_MAP[status]?.name || 'Desconhecido',
      color: STATUS_MAP[status]?.color || '#6c757d',
      count: grupos[status].length,
      items: grupos[status]
    }));
  };

  const formatarData = (dataString: string) => {
    if (!dataString) return 'Não definido';

    const data = new Date(dataString);
    return data.toLocaleDateString('pt-BR') + ' ' + data.toLocaleTimeString('pt-BR', {
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  useEffect(() => {
    buscarOrdens();
  }, []);

  const ordensAgrupadas = agruparPorStatus();

  if (loading && ordensServico.length === 0) {
    return (
      <Container>
        <Header>
          <AppName>OS <Text style={{ fontWeight: 'bold' }}>Control</Text></AppName>
          <TouchableOpacity onPress={handleAvatarPress}>
            <Avatar source={require('../../assets/avatar.png')} />
          </TouchableOpacity>
        </Header>
        <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center' }}>
          <ActivityIndicator size="large" color="#ff8403" />
          <Text style={{ marginTop: 10, color: '#666' }}>
            Carregando suas ordens de serviço...
          </Text>
        </View>
      </Container>
    );
  }

  return (
    <Container>
      <Header>
        <AppName>OS <Text style={{ fontWeight: 'bold' }}>Control</Text></AppName>
        <View style={{ flexDirection: 'row', alignItems: 'center' }}>
          <TouchableOpacity
            onPress={handleLogout}
            style={{
              marginRight: 15,
              paddingHorizontal: 12,
              paddingVertical: 6,
              backgroundColor: '#ff8403',
              borderRadius: 15
            }}
          >
            <Text style={{ color: '#fff', fontSize: 12, fontWeight: 'bold' }}>
              Sair
            </Text>
          </TouchableOpacity>
          <TouchableOpacity onPress={handleAvatarPress}>
            <Avatar source={require('../../assets/avatar.png')} />
          </TouchableOpacity>
        </View>
      </Header>

      <SearchBar>
        <TextInput
          placeholder="Buscar por título, descrição ou cliente"
          style={{ flex: 1, fontSize: 16 }}
          value={searchText}
          onChangeText={setSearchText}
          returnKeyType="search"
        />
      </SearchBar>

      <View style={{
        flexDirection: 'row',
        justifyContent: 'space-between',
        paddingHorizontal: 20,
        paddingVertical: 10,
        backgroundColor: '#f8f9fa'
      }}>
        <Text style={{ color: '#666', fontSize: 14 }}>
          Total: {ordensServico.length} ordem(ns)
        </Text>
        {searchText && (
          <Text style={{ color: '#666', fontSize: 14 }}>
            Filtradas: {ordensAgrupadas.reduce((acc, section) => acc + section.count, 0)}
          </Text>
        )}
      </View>

      <ScrollView
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={refresh}
            colors={['#ff8403']}
            tintColor="#ff8403"
          />
        }
        showsVerticalScrollIndicator={false}
      >
        {error && (
          <View style={{
            margin: 20,
            padding: 15,
            backgroundColor: '#f8d7da',
            borderRadius: 8,
            borderColor: '#dc3545',
            borderWidth: 1
          }}>
            <Text style={{ color: '#721c24', textAlign: 'center' }}>
              {error}
            </Text>
          </View>
        )}

        {ordensAgrupadas.length === 0 ? (
          <View style={{ padding: 40, alignItems: 'center' }}>
            <Text style={{ fontSize: 16, color: '#666', textAlign: 'center' }}>
              {searchText
                ? 'Nenhuma ordem encontrada para essa busca'
                : 'Você não possui ordens de serviço no momento'
              }
            </Text>
          </View>
        ) : (
          ordensAgrupadas.map(section => (
            <Section key={section.status}>
              <SectionHeader>
                <SectionTitle style={{ color: section.color }}>
                  {section.statusName}
                </SectionTitle>
                <StatusCount style={{ backgroundColor: section.color }}>
                  {section.count}
                </StatusCount>
              </SectionHeader>

              {section.items.map((item: any) => {
                const consumidor = item.dados?.informacoes_consumidor || {};
                const produto = item.dados?.informacoes_produto || {};
                const os = item.dados?.informacoes_os || {};
                const agendamento = item.dados?.informacoes_agendamento || {};

                return (
                <Card key={item.ticket}>
                  <View style={{
                    flexDirection: 'row',
                    justifyContent: 'space-between',
                    alignItems: 'flex-start',
                    marginBottom: 8
                  }}>
                    <Text style={{
                      fontWeight: 'bold',
                      fontSize: 16,
                      flex: 1,
                      color: '#333'
                    }}>
                      Ticket #{item.ticket} - OS #{item.os}
                    </Text>
                    <Tag style={{ backgroundColor: '#17a2b8' }}>
                      NF: {os.nota_fiscal || 'N/A'}
                    </Tag>
                  </View>

                  <Text style={{
                    marginBottom: 6,
                    color: '#007bff',
                    fontWeight: '600'
                  }}>
                    📅 Agendamento: {agendamento.data_agendamento ?
                      `${agendamento.data_agendamento} ${agendamento.hora_inicio || ''}` :
                      'Não agendado'}
                  </Text>

                  <Text style={{
                    marginBottom: 10,
                    color: '#666',
                    lineHeight: 20
                  }}>
                    🔧 {produto.produto_descricao || 'Produto não informado'}
                  </Text>

                  <View style={{
                    flexDirection: 'row',
                    alignItems: 'center',
                    marginBottom: 6
                  }}>
                    <Text style={{ fontWeight: 'bold', color: '#333' }}>
                      👤 {consumidor.nome_consumidor || 'Consumidor não informado'}
                    </Text>
                  </View>

                  <Address>
                    📍 {consumidor.endereco_consumidor ?
                      `${consumidor.endereco_consumidor}, ${consumidor.numero_consumidor || 's/n'} - ${consumidor.bairro_consumidor || ''}, ${consumidor.cidade_consumidor || ''}-${consumidor.estado_consumidor || ''}` :
                      'Endereço não informado'}
                  </Address>

                  <View style={{
                    flexDirection: 'row',
                    justifyContent: 'space-between',
                    alignItems: 'center',
                    marginTop: 12,
                    paddingTop: 12,
                    borderTopWidth: 1,
                    borderTopColor: '#eee'
                  }}>
                    <Text style={{ fontSize: 12, color: '#999' }}>
                      Criado: {item.data_input ? formatarData(item.data_input) : 'N/A'}
                    </Text>

                    {renderBotaoAcao(item)}
                  </View>
                </Card>
              );
              })}
            </Section>
          ))
        )}

        <View style={{ height: 20 }} />
      </ScrollView>

      {/* Modal de Perfil do Técnico */}
      <Modal
        visible={showProfileModal}
        transparent={true}
        animationType="slide"
        onRequestClose={() => setShowProfileModal(false)}
      >
        <View style={{
          flex: 1,
          backgroundColor: 'rgba(0,0,0,0.5)',
          justifyContent: 'center',
          alignItems: 'center',
          padding: 20
        }}>
          <View style={{
            backgroundColor: '#fff',
            borderRadius: 20,
            padding: 25,
            width: '100%',
            maxWidth: 400,
            maxHeight: '80%',
            shadowColor: '#000',
            shadowOffset: { width: 0, height: 4 },
            shadowOpacity: 0.3,
            shadowRadius: 10,
            elevation: 10
          }}>
            {/* Header do Modal */}
            <View style={{
              flexDirection: 'row',
              justifyContent: 'space-between',
              alignItems: 'center',
              marginBottom: 20,
              paddingBottom: 15,
              borderBottomWidth: 2,
              borderBottomColor: '#ff8403'
            }}>
              <Text style={{
                fontSize: 22,
                fontWeight: 'bold',
                color: '#333'
              }}>
                Meu Perfil
              </Text>
              <TouchableOpacity
                onPress={() => setShowProfileModal(false)}
                style={{
                  backgroundColor: '#f5f5f5',
                  borderRadius: 20,
                  width: 35,
                  height: 35,
                  justifyContent: 'center',
                  alignItems: 'center'
                }}
              >
                <Text style={{ fontSize: 20, color: '#666' }}>✕</Text>
              </TouchableOpacity>
            </View>

            {/* Conteúdo do Modal */}
            {loadingTecnico ? (
              <View style={{ padding: 40, alignItems: 'center' }}>
                <ActivityIndicator size="large" color="#ff8403" />
                <Text style={{ marginTop: 15, color: '#666' }}>
                  Carregando dados...
                </Text>
              </View>
            ) : tecnicoData ? (
              <ScrollView showsVerticalScrollIndicator={false}>
                {/* Avatar Central */}
                <View style={{ alignItems: 'center', marginBottom: 20 }}>
                  <View style={{
                    width: 100,
                    height: 100,
                    borderRadius: 50,
                    backgroundColor: '#ff8403',
                    justifyContent: 'center',
                    alignItems: 'center',
                    marginBottom: 10
                  }}>
                    <Text style={{ fontSize: 40, color: '#fff' }}>
                      {tecnicoData.data?.nome?.charAt(0)?.toUpperCase() || '👤'}
                    </Text>
                  </View>
                  <Text style={{
                    fontSize: 20,
                    fontWeight: 'bold',
                    color: '#333',
                    textAlign: 'center'
                  }}>
                    {tecnicoData.data?.nome || 'Não informado'}
                  </Text>
                  <Text style={{
                    fontSize: 14,
                    color: '#666',
                    marginTop: 5
                  }}>
                    {tecnicoData.data?.login || 'N/A'}
                  </Text>
                </View>

                {/* Informações */}
                <View style={{ gap: 15 }}>
                  <View style={{
                    backgroundColor: '#f8f9fa',
                    padding: 15,
                    borderRadius: 12,
                    borderLeftWidth: 4,
                    borderLeftColor: '#ff8403'
                  }}>
                    <Text style={{ fontSize: 12, color: '#666', marginBottom: 5 }}>
                      ID
                    </Text>
                    <Text style={{ fontSize: 16, color: '#333', fontWeight: '500' }}>
                      #{tecnicoData.data?.usuario || 'N/A'}
                    </Text>
                  </View>

                  <View style={{
                    backgroundColor: '#f8f9fa',
                    padding: 15,
                    borderRadius: 12,
                    borderLeftWidth: 4,
                    borderLeftColor: '#17a2b8'
                  }}>
                    <Text style={{ fontSize: 12, color: '#666', marginBottom: 5 }}>
                      TIPO
                    </Text>
                    <Text style={{ fontSize: 16, color: '#333', fontWeight: '500' }}>
                      {tecnicoData.data?.tecnico ? 'Técnico' : 'Usuário'}
                    </Text>
                  </View>

                  <View style={{
                    backgroundColor: '#f8f9fa',
                    padding: 15,
                    borderRadius: 12,
                    borderLeftWidth: 4,
                    borderLeftColor: tecnicoData.data?.ativo ? '#28a745' : '#dc3545'
                  }}>
                    <Text style={{ fontSize: 12, color: '#666', marginBottom: 5 }}>
                      STATUS
                    </Text>
                    <View style={{ flexDirection: 'row', alignItems: 'center' }}>
                      <View style={{
                        width: 10,
                        height: 10,
                        borderRadius: 5,
                        backgroundColor: tecnicoData.data?.ativo ? '#28a745' : '#dc3545',
                        marginRight: 8
                      }} />
                      <Text style={{
                        fontSize: 16,
                        color: tecnicoData.data?.ativo ? '#28a745' : '#dc3545',
                        fontWeight: '600'
                      }}>
                        {tecnicoData.data?.ativo ? 'Ativo' : 'Inativo'}
                      </Text>
                    </View>
                  </View>
                </View>
              </ScrollView>
            ) : (
              <View style={{ padding: 40, alignItems: 'center' }}>
                <Text style={{ color: '#666', textAlign: 'center' }}>
                  Nenhum dado disponível
                </Text>
              </View>
            )}
          </View>
        </View>
      </Modal>
    </Container>
  );
}