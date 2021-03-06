=== Plugin Name ===
Contributors: iamport
Donate link: http://www.iamport.kr
Tags: woocommerce, commerce, payment, checkout, 카카오페이, 페이코, payco, kakao, kakaopay, 이니시스, kpay, inicis, 유플러스, lguplus, uplus, 나이스, 나이스페이, nice, nicepay, 제이티넷, 티페이, jtnet, tpay, 다날, danal, 모빌리언스, mobilians, 정기결제, subscription, 해외카드, visa, master, jcb, shopping, mall, iamport
Requires at least: 3.5
Tested up to: 4.5.3
Stable tag: 1.7.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

우커머스용 결제 플러그인. 신용카드/실시간이체/가상계좌/휴대폰소액결제 가능. 국내 여러 PG사를 지원.

== Description ==

아임포트는 국내 PG서비스들을 표준화하고 있는 결제 서비스입니다. 아임포트 하나면 국내 여러 PG사들의 결제 기능을 표준화된 동일한 방식으로 사용할 수 있게 됩니다. 
이 플러그인은 아임포트 서비스를 우커머스(woocommerce)환경에 맞게 적용한 결제 플러그인입니다.
신용카드 / 실시간계좌이체 / 가상계좌 / 휴대폰소액결제를 지원합니다. <br>
현재는, "삼성페이", "PAYCO(페이코)", "카카오페이", "KG이니시스", "LGU+", "나이스정보통신", "JTNet(tPay)", "다날", "모빌리언스(휴대폰소액결제)"를 지원하고 있습니다.
우커머스 정기결제 플러그인도 지원하고 있습니다. 

1.4.2 버전부터는 다국어 지원이 가능합니다. 언어별 번역 프로젝트에 참여를 부탁드립니다.

1.4.1 버전부터는 Woocommerce Subscription(우커머스 정기결제)기능과 JTNet을 통한 해외카드결제(VISA/MASTER/JCB)카드결제도 지원합니다. (전달되는 카드정보는 워드프레스 내에 저장되지 않고 폐기되며 암호화되어 전송되며 SSL통신을 적용합니다)

http://www.iamport.kr 에서 아임포트 서비스에 대한 보다 상세한 내용을 확인하실 수 있습니다.

데모 페이지 : http://demo.movingcart.kr

*   아임포트 관리자 페이지( https://admin.iamport.kr ) 에서 관리자 회원가입을 합니다.
*   아임포트 플러그인을 다운받아 워드프레스에 설치합니다.
*   아임포트 시스템설정 페이지에서 "가맹점 식별코드", "REST API키", "REST API secret"을 플러그인 설정에 저장합니다.


== Installation ==

아임포트 플러그인 설치, https://admin.iamport.kr 에서 관리자 회원가입, 시스템설정 정보저장이 필요합니다.


1. 다운받은 iamport.zip파일을 `/wp-content/plugins/` 디렉토리에 복사합니다.
2. unzip iamport.zip으로 압축 파일을 해제하면 iamport폴더가 생성됩니다.
3. 워드프레스 관리자페이지에서 'Plugins'메뉴를 통해 "아임포트" 플러그인을 활성화합니다. 
4. https://admin.iamport.kr 에서 관리자 회원가입 후 시스템설정 페이지의 "가맹점 식별코드", "REST API키", "REST API secret"를 확인합니다.
5. 우커머스(woocommerce) 결제 설정페이지에서 해당 정보를 저장합니다.

== Frequently Asked Questions ==
= 서비스 소개 =
http://www.iamport.kr
= 관리자 페이지 =
https://admin.iamport.kr
= 페이스북 =
https://www.facebook.com/iamportservice

= 고객센터 =
1670-5176 / cs@iamport.kr

== Screenshots ==
1. 아임포트 관리자 로그인 후 "시스템 설정" 페이지에서 "가맹점 식별코드", "REST API키", "REST API secret" 정보를 확인합니다.
2. 우커머스(woocommerce) 결제 설정 페이지에서 "가맹점 식별코드", "REST API키", "REST API secret" 정보를 저장합니다.


== Changelog ==
= 1.7.6 =
* 해외카드 결제 Gateway, 환불 API누락되어있어 추가 구현

= 1.7.5 =
* 모바일에서 "결제창방식 정기결제"완료 후 백버튼으로 뒤로 이동하면, 주문 상태가 "실패함"으로 바뀌는 버그 수정
* "결제창방식 정기결제"는 장바구니에 정기결제 상품이 담겨져있지 않으면 결제수단에 표시되지 않도록 개선
* 결제 주문명 커스터마이즈할 수 있도록 filter 정의
* 구매자 환불 프로세스에 대한 설명 보완해서 혼선 방지

= 1.7.4 =
* pg\_tid 필드 메타정보에 추가

= 1.7.3 =
* action hook 추가 정의(매뉴얼화)
* 에러 로깅 보강

= 1.7.2 =
* 다날-휴대폰소액결제 정기결제 추가
* 결제창 방식의 정기결제 시 금액 0원일 필요가 없는 경우에는 상품 원래 가격 그대로 표시

= 1.7.1 =
* 정기결제(결제창방식) 복수PG로 세팅되는 경우가 많으므로 PG상점아이디 추가로 받아서 복수PG사용 가능하도록 설정

= 1.7.0 =
* JTNet, KG이니시스, 다날과 같이 PG사 결제창을 통한 정기결제 방식도 지원

= 1.6.23 =
* 플러그인 영문 번역파일 추가

= 1.6.22 =
* jQuery 버전 체크해서 iOS카카오페이 바로오픈하는 속성 추가

= 1.6.21 =
* 다른 플러그인에서 billing_phone(우커머스 기본 required field)를 required항목에서 제외하고 입력창을 제거하는 경우가 있음.(ex. BEOMPS) 이 경우 KG이니시스 등 일부 PG사에서 buyer_tel누락오류가 발생할 수 있어 dummy number추가

= 1.6.20 =
* 모빌리언스도 휴대폰 소액결제 가능하도록 플러그인 업데이트

= 1.6.19 =
* (1.6.16기능 보충)아임포트 관리자 페이지에서 부분취소를 하는 경우에도 그 기록이 모두 저장될 수 있도록 수정.(환불 가능한 잔액이 남아있을 때까지는 결제 상태를 "환불됨"으로 바꾸지 않음)

= 1.6.18 =
* #order\_review관련 handler등록 방식 변경. 1.6.13버전 패치보다 안정적인 방식으로 변경(체크아웃 페이지에서 id="order\_review" name="check" 2가지 속성을 모두 가진 테마가 종종 발견됨)

= 1.6.17 =
* 테마에 따라서 submit을 중단해도 다른 submit handler가 submit해버리는 경우가 발생됨. stopImmediatePropagation() 호출로 방지

= 1.6.16 =
* 아임포트 관리자 페이지에서 취소하기로 환불하는 경우에도 우커머스 주문상태 변경될 수 있도록 Notification 구현
* 복수PG설정된 경우 WC\_Gateway\_Iamport\_Vbank 가 호출되더라도 실제 해당 결제건의 gateway를 찾아서 정확하게 REST API key, secret을 적용할 수 있도록 수정
* 아임포트가 주문상태를 변경할 때 do_action 할 수 있도록 추가(iamport\_order\_status\_changed)

= 1.6.15 =
* woocommerce 2.6부터 WC\_Payment\_Gateway\_CC->form을 사용해야 함

= 1.6.14 =
* 정기결제에서 free trial로 최초 결제할 금액이 없는 경우 빌링키 등록과정에서 실제 결제까지 가능한 카드인지 체크할 수 있도록 테스트 결제기능 추가

= 1.6.13 =
* 정기결제에서 free trial로 최초 결제할 금액이 없는 경우 빌링키 등록이 안되는 버그 수정(빌링키 등록 과정에서 카드정보 유효성 체크)
* Payment Form상태에서 KEY-IN결제도 ajax방식으로 인터페이스 공통화
* ajax 응답에 dummy string이 앞뒤에 있어도 필터링하여 처리될 수 있도록 수정
* 테마에 따라 카드결제정보 중복으로 발송될 수 있는 경우에 대한 대비(#order_review)

= 1.6.12 =
* 결제설정관련 "아임포트" 탭으로 정리
* 워드프레스 언어 설정이 한글이 아닌 경우 PG영문창 띄울 수 있도록 language설정(PG사가 영어지원을 하는 경우에 한해 적용됨)
* Woocommerce에 부가세 별도 설정이 활성화된 경우 vat파라메터 지정(면세, 일부 면세 등 사용 가능)
* 환불/교환버튼 비활성화를 원할 경우 처리할 수 있도록 설정기능 추가

= 1.6.11 =
* 정기결제에서 automatic retry for failed payment기능 사용가능하도록 subscription\_date\_changes 추가

= 1.6.10 =
* 1.6.9버전에서 설치 직후 아무런 세팅값을 설정하지 않았을 때, 기본값이 비활성화이어야 하는데 활성화로 처리되는 버그 수정

= 1.6.9 =
* "처리중"(결제가 완료됨을 의미) 상태의 주문을 "완료됨"(상품발송이 완료됨을 의미) 상태로 자동 변경하는 옵션 추가(결제 즉시 서비스가 개시되어야하는 경우 활용)

= 1.6.8 =
* 1.6.5의 woocommerce status변경에 대한 동시성 제어 버그 발견되어 수정

= 1.6.7 =
* 1.6.3에서 수정된 결제수단정보 업데이트 추가 버그 발견되어 수정(Notification이 먼저 도착하는 경우 항상 가상계좌로 처리되어버림)

= 1.6.6 =
* KEY-IN결제의 경우 비회원도 결제 가능하도록 
* woocommerce order_key생성 규칙 더욱 복잡하게(중복발생 안되도록)

= 1.6.5 =
* 판매자가 가상계좌 입금기한을 제한할 수 있도록 설정기능 추가
* woocommerce status변경에 대해 동시성 제어를 위해 lock 기능 추가

= 1.6.4 =
* 비인증결제 KEY-IN/빌링방식 모두 지원(Woocommerce-Subscription플러그인 없이도 KEY-IN은 사용 가능하도록)
* PAYCO결제수단 추가
* PROXY환경에서 curl통신가능하도록 수정
* 가상계좌 입금시 통지되는 관리자 이메일, 이메일 타입 지정가능

= 1.6.3 =
* timezone offset을 고려하여 시각 정보 출력하도록 수정
* 관리자 페이지내 주문정보에 가상계좌 발급정보 같이 출력
* Checkout페이지에서 결제 중단 후 마이페이지에서 재결제를 최초와 다른 결제수단으로 진행하였을 때 결제수단정보 업데이트

= 1.6.2 =
* Notification이 너무 일찍 도달하는 경우 check\_payment\_response()가 동시에 두 번 호출되는 경우가 생김. (mysql동시성 해결전까지 pay_method로 필터링)
* 우커머스 서브스크립션 라벨 변경

= 1.6.1 =
* 결제수단으로 삼성페이 추가(KG이니시스 계약 필요)

= 1.6.0 =
* 복수PG사용자들이 결제수단별로 원하는 PG사 설정이 가능하도록 pg_provider 설정 추가

= 1.5.13 =
* 다날 신용카드/계좌이체/가상계좌 지원(다날 가상계좌의 경우 사업자등록번호 필수)

= 1.5.12 =
* wc-api 파라메터 추가하는 방식 변경
* 기본 출력 메세지 변경

= 1.5.11 =
* description이 선언되지 않은 결제수단 설정 필드 PHP Notice 발생되지 않도록 수정

= 1.5.10 =
* 상품이 여러 개 주문될 때 주문명이 잘못 만들어지는 버그 수정
* 플러그인 정보 확인용 버전 출력

= 1.5.9 =
* 중복 redirect 등의 이유로 결제 프로세스 도중 check\_payment\_response()가 여러 번 호출되더라도 문제없도록 redirect기능 추가

= 1.5.8 =
* 우커머스 정기결제(woocommerce-subscription) 재결제 후 active상태로 전환되지 않는 버그 수정
* 우커머스 정기결제(woocommerce-subscription) 취소/환불기능 지원

= 1.5.7 =
* 워드프레스가 설치된 서버 환경에 따라 아임포트 서버와의 REST API통신에 실패하는 경우가 있어 curl 설치 및 SSL접속 테스트 기능 추가

= 1.5.6 =
* 중단되었던 결제를 나의결제 페이지에서 다시 재시도할 때 escrow파라메터 제대로 세팅되지 않는 버그 수정
* 가상계좌 입금통지 설정 안내 추가(아임포트 Notification URL설정)

= 1.5.5 =
* 결제완료여부 확인할 때 파라메터 조작하여 미결제내역을 결제완료로 바꾸려는 시도에 대해 방어하도록 로직 수정
* 아임포트 Notification URL호출되었을 때 application/json타입으로 전달 될 수도 있어 대응하도록 수정

= 1.5.4 =
* hotfix : sprintf함수 인자에 %s에서 s가 빠지는 바람에 소스코드 인코딩에 따라 한글이 깨져서 post가 안되는 버그 수정

= 1.5.3 =
* $\_REQUEST 변수 사용하지 않도록 수정($\_GET, $\_POST를 직접 사용하도록)
* 가상계좌 입금통보 처리 로직 버그 수정

= 1.5.2 =
* 사용 중인 jquery-confirm.js가 boostrap때문에 일부 테마와 충돌나는 경우가 발생. 기본 jquery modal사용으로 변경(bootstrap기반 외부 라이브러리 제거)
* language번역 함수에 포함되지 않은 텍스트 수정

= 1.5.0 =
* 상품발송이 완료된(주문상태가 완료-Completed) 주문건에 대해 구매자가 판매자에게 반품 또는 교환을 요청할 수 있는 기능 추가(jquery 1.8이상 버전이 필요해 wordpress 최소 버전 3.5로 수정)
* 반폼 또는 교환 요청 시 요청 사유를 직접 기입할 수 있도록 기능 제공
* 반품요청/교환요청 상태가 추가되어 판매자 우커머스 주문목록에서 조회 가능

= 1.4.3 =
* 우커머스 정기결제(서브스크립션)에서 jQuery Card formatting잘못되는 버그 수정

= 1.4.2 =
* 다국어 지원 (번역파일은 부재)

= 1.4.1 =
* JTNet을 통한 해외카드(VISA/MASTER/JCB) 결제기능 지원
* 정기결제 / 해외카드 결제시 전송되는 카드정보에 RSA암호화 적용

= 1.4.0 =
* 우커머스 정기결제(woocommerce-subscription) 플러그인 기능 제공
* 구매자가 my-page에서 결제한 내역 직접 취소가능(processing-처리중 상태일 때만 가능. 서비스 제공 후 취소를 막기 위해 completed-완료됨 상태일 때는 불가능)

= 1.3.12 =
* 구매자 성명에서 이름 / 성이 반대로 출력되는 버그 수정(강동훈님 제보)

= 1.3.11 =
* 관리자에게 발송되는 가상계좌 입금확인 통지 Email에서 HTML/PlainText타입선택 옵션 제거. woocommerce 2.4.10부터 제공되는 기능이라 하위호환성차원에서 제거

= 1.3.10 =
* woocommerce 2.2 / 2.3에서는 ajax response시 dummy text를 앞뒤로 보내줘서 json parsing을 고의적으로 방해함. 해당 dummy제거를 위해 ajax dataFilter설정

= 1.3.9 =
* KG이니시스의 KPay간편결제를 결제수단으로 추가할 수 있도록 gateway추가.
* 기존 KG이니시스 결제창 내에서 KPay를 선택해서 사용할 수도 있었으나 KPay를 구매자에게 명시적으로 노출하기 위한 용도로 사용(Mac에서는 KG이니시스에서 KPay를 지원하지 않음)

= 1.3.8 =
* 1.3.6버전에서 추가된 기능("대기중" 상태의 주문을 다시 지불하려고 할 때)에서 카카오페이의 경우 제대로 동작하지 않는 버그 수정
* process_payment()에서 redirect파라메터를 재결제페이지로 이동(iamport.woocommerce.js에서 submit event를 잡아내는데 실패하더라도 재결제가 가능하도록)

= 1.3.7 =
* 휴대폰소액결제 전문 PG사인 다날을 이용해 별도의 휴대폰소액결제 서비스를 이용하시려는 사용자를 위한 설정기능 추가

= 1.3.6 =
* "나의계정" 페이지로부터 "대기중"상태의 주문을 다시 지불하려고 할 때 결제창이 안뜨는 문제 해결 ( order_review페이지에서 결제 가능하도록 수정 )

= 1.3.5 =
* 휴대폰 소액결제에서 실물/디지털 상품 구분필드 적용(휴대폰 소액결제의 경우 실물 상품인지 디지털 상품인지 구분해서 결제창 요청해야합니다. 상품에 따라 결제수수료가 달라지는 부분이라 통신사가 심사 단계에서 필수로 요구하는 사항입니다)

= 1.3.4 =
* 가상계좌 입금대기중(awaiting-vbank) 상태에서 처리중(processing) 또는 완료됨(completed) 상태로 변경될 때 관리자/구매자에게 이메일 발송(입금완료되었음을 알리는 메일)
* 우커머스 이메일 설정에 가상계좌 입금완료 시 관리자/구매자에게 발송되는 설정 추가

= 1.3.3 =
* php short_open_tag 설정이 off인 경우에 가상계좌 정보 출력 로직이 제대로 안되는 버그 수정
* 결제 상세 정보가 중복으로 출력되는 버그 수정

= 1.3.2 =
* icon, banner추가
* 다른 플러그인의 iamport.php와 중복 로딩되는 것 방지

= 1.3.1 =
* 카카오페이 결제구분을 pay_method로 하지 않고 pg_provider로 하도록 변경. 카카오페이로 결제했더라도 pay_method는 card를 유지

= 1.3.0 =
* 카카오페이 결제수단 추가
* iamport.woocommerce.js 에서 가격에 소수점이 있는 경우 결제 오류나는 경우를 대비해 parseInt처리하여 방어적으로 변경(우커머스 소수점 자리수 설정 방지)
* iamport.woocommerce.js 에서 try catch문 후처리 과정에서 exception message출력하도록 수정(기존에 undefined변수를 출력하고 있었음)
* iamport.payment-1.1.0.js 로 아임포트 javascript API라이브러리 버전업

= 1.2.2 =
* ajax place order를 위해 추가된 iamport.woocommerce.js에서 사용하던 wc_checkout_form객체는 2.2.x버전에서는 지원되지 않는 객체여서 2.2.x호환되는 방식으로 변환

= 1.2.1 =
* 가상계좌 정보출력 소스코드(iamport-vbank.php)에서 syntax 에러가 발견되어 수정
* stable version 4.4로 상향

= 1.2 =
* 주문서 작성 후 "주문 확정" 단계에서 결제수단별(신용카드/실시간계좌이체/가상계좌/휴대폰소액결제) 분리
* 가상계좌 선택 시 결제 완료 페이지에서 입금계좌번호 출력 안되는 버그 수정
* 결제 내역 페이지에서도 결제정보 출력

= 1.1 =
* LGU+추가 지원
* "가상계좌 입금대기 중" 주문 상태 추가 정의
* 가상계좌 입금완료 처리로직 누락된 부분 추가
* 결제완료 후 결제 상세 정보 및 매출전표 확인 링크 출력
* 가상계좌 결제 시 구매자가 계좌정보를 확인할 수 있도록 주문접수 페이지에서 가상계좌 입금정보 노출

= 1.0 =
* 결제수단 선택 UI개선
* 에스크로결제 제공

= 0.9 =
* 최초 배포
* http://demo.movingcart.kr 에 적용된 버전


== Action Hook ==

= 아임포트 for 우커머스 플러그인이 제공하는 action hook =
*   `iamport_order_status_changed` : 아임포트에 의해 우커머스 주문 상태가 변경되었을 때 호출($old\_status, $new\_status) 2개의 파라메터 제공
*   `iamport_order_meta_saved` : 아임포트와의 통신 후 주문에 대한 부가 정보를 저장할 때 호출($order\_id, $meta\_key, $meta\_value) 3개의 파라메터 제공 (meta\_key목록 아래 참조)
    *   \_iamport\_provider : 결제된 PG사코드
    *   \_iamport\_paymethod : 결제수단
    *   \_iamport\_pg\_tid : 결제건에 대한 PG사 승인번호
    *   \_iamport\_receipt\_url : 결제건에 대한 매출전표 URL
    *   \_iamport\_vbank_name : (가상계좌 결제 시)발급된 가상계좌 은행명
    *   \_iamport\_vbank_num : (가상계좌 결제 시)발급된 가상계좌 번호
    *   \_iamport\_vbank_date : (가상계좌 결제 시)발급된 가상계좌의 입금기한(unix timestamp)
*   `iamport_simple_order_name` : 일반 상품 주문시 적용되는 상품명 filter($order\_name, $order) 2개의 파라메터 제공
*   `iamport_recurring_order_name` : 정기결제 상품 주문시 적용되는 상품명 filter($order\_name, $order, $isInitial) 3개의 파라메터 제공

