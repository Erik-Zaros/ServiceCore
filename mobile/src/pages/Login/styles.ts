import styled from 'styled-components/native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { COLORS } from '../../config/constants';

export const Container = styled(SafeAreaView)`
  flex: 1;
  background-color: ${COLORS.white};
`;

export const Content = styled.KeyboardAvoidingView`
  flex: 1;
  padding: 20px;
  justify-content: center;
`;

export const Header = styled.View`
  align-items: center;
  margin-bottom: 10px;
  margin-top: -150px;
`;

export const Title = styled.Text`
  font-size: 32px;
  font-weight: bold;
  color: ${COLORS.secondary};
  margin-bottom: 10px;
`;

export const Subtitle = styled.Text`
  font-size: 16px;
  margin-top: -50px;
  color: ${COLORS.text};
`;

export const Form = styled.View`
  width: 100%;
  margin-top: 5px; 
`;

export const Input = styled.TextInput`
  background-color: ${COLORS.background};
  border-radius: 8px;
  padding: 15px;
  margin-bottom: 15px;
  font-size: 16px;
  color: ${COLORS.text};
  border-width: 1px;
  border-color: ${COLORS.border};
`;

export const Button = styled.TouchableOpacity<{ disabled?: boolean }>`
  background-color: ${props => props.disabled ? COLORS.disabled : COLORS.primary};
  border-radius: 8px;
  padding: 15px;
  align-items: center;
  margin-top: 10px;
`;

export const ButtonText = styled.Text`
  color: ${COLORS.white};
  font-size: 16px;
  font-weight: bold;
`; 