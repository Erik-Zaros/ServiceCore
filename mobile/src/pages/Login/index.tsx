import React, { useState } from 'react';
import { Platform, Alert, Image } from 'react-native';
import { authService } from '../../services/authService';
import { Container, Content, Header, Subtitle, Form, Input, Button, ButtonText } from './styles';
import { useNavigation } from '@react-navigation/native';

const Login: React.FC = () => {
    const [login, setLogin] = useState('');
    const [senha, setSenha] = useState('');
    const [loading, setLoading] = useState(false);

    const navigation = useNavigation<any>();

    const handleLogin = async () => {
        if (!login.trim() || !senha.trim()) {
            Alert.alert('Erro', 'Por favor, preencha todos os campos');
            return;
        }

        setLoading(true);
        try {
            await authService.login({ 
                login: login.trim(), 
                senha: senha 
            });

            Alert.alert('Sucesso', 'Login realizado com sucesso!');
            navigation.navigate('Dashboard');

        } catch (error) {
            Alert.alert(
                'Erro',
                error instanceof Error ? error.message : 'Não foi possível fazer login. Tente novamente.'
            );
        } finally {
            setLoading(false);
        }
    };

    return (
        <Container>
            <Content behavior={Platform.OS === 'ios' ? 'padding' : 'height'}>
                <Header>
                    <Image
                        source={require('../../assets/serviceCore.png')}
                        style={{ width: 250, height: 250, resizeMode: 'contain' }}
                    />
                    <Subtitle>Faça login para continuar</Subtitle>
                </Header>

                <Form>
                    <Input
                        placeholder="Login"
                        value={login}
                        onChangeText={setLogin}
                        keyboardType="email-address"
                        autoCapitalize="none"
                        autoCorrect={false}
                        editable={!loading}
                    />

                    <Input
                        placeholder="Senha"
                        value={senha}
                        onChangeText={setSenha}
                        secureTextEntry
                        autoCapitalize="none"
                        autoCorrect={false}
                        editable={!loading}
                    />

                    <Button onPress={handleLogin} disabled={loading}>
                        <ButtonText>
                            {loading ? 'Carregando...' : 'Entrar'}
                        </ButtonText>
                    </Button>
                </Form>
            </Content>
        </Container>
    );
};

export default Login;