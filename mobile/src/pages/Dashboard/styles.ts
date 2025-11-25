import styled from 'styled-components/native';
import { COLORS } from '../../config/constants';

export const Container = styled.View`
  flex: 1;
  background: #f5f5f5;
`;

export const Header = styled.View`
  background: ${COLORS.primary};
  padding: 40px 16px 16px 16px;
  flex-direction: row;
  align-items: center;
  justify-content: space-between;
`;

export const AppName = styled.Text`
  color: #fff;
  font-size: 22px;
`;

export const Avatar = styled.Image`
  width: 40px;
  height: 40px;
  border-radius: 20px;
  margin-left: 12px;
`;

export const SearchBar = styled.View`
  background: #fff;
  margin: 12px 16px;
  border-radius: 8px;
  flex-direction: row;
  align-items: center;
  padding: 0 12px;
  elevation: 2;
`;

export const Section = styled.View`
  margin-bottom: 16px;
`;

export const SectionHeader = styled.View`
  flex-direction: row;
  align-items: center;
  background: ${COLORS.primary};
  padding: 4px 16px;
  border-top-left-radius: 8px;
  border-top-right-radius: 8px;
  margin: 0 16px;
`;

export const SectionTitle = styled.Text`
  color: #fff;
  font-size: 16px;
  flex: 1;
`;

export const StatusCount = styled.Text`
  background: #fff;
  color: ${COLORS.primary};
  border-radius: 12px;
  padding: 2px 8px;
  margin-left: 8px;
  font-weight: bold;
`;

export const Card = styled.View`
  background: #fff;
  margin: 0 16px 8px 16px;
  padding: 12px;
  border-radius: 8px;
  elevation: 1;
`;

export const Name = styled.Text`
  color: ${COLORS.primary};
  font-weight: bold;
  margin-right: 8px;
`;

export const Tag = styled.Text`
  color: #0068d4;
  background: #e6f0fa;
  border-radius: 6px;
  padding: 2px 6px;
  margin-left: 4px;
`;

export const Address = styled.Text`
  color: #6C757D;
  font-size: 12px;
  margin-top: 4px;
`;