<?php
namespace sammo\Enums;

/**
 * API 호출 결과가 false이면서, 대상 API가 처리할 수 없는 추가 동작이 있는 경우 사용.
 * 예시: 턴을 예약해야 하지만, 장수가 사망하여 게이트웨이로 이동해야함.
 */
enum APIRecoveryType: string{
  case Login = 'login';//로그인 재시도부터 시작한다
  case TwoFactorAuth = '2fa';//로그인은 가능하지만 인증코드 입력이 필요하다
  case Gateway = 'gateway';//로그인 완료. 게임 서버 리스트를 띄워야한다
  case GameLogin = 'game_login';//서버 업데이트 등으로 다시 게임 정보 수신 필요
  case GameQuota = 'game_quota';//접속 제한
}