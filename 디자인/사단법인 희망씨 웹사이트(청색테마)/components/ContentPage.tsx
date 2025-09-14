import { ArrowLeft, MapPin, Phone, Mail, Calendar, FileText, Heart, Globe, Users, Handshake, Star, Building } from "lucide-react";
import { Button } from "./ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "./ui/card";
import { Separator } from "./ui/separator";

interface ContentPageProps {
  page: string;
  onPageChange: (page: string) => void;
}

export default function ContentPage({ page, onPageChange }: ContentPageProps) {
  const renderContent = () => {
    switch (page) {
      case "희망씨는":
        return (
          <div className="space-y-8">
            <div className="text-center">
              <h1 className="text-5xl text-blue-700 font-bold mb-4">희망씨는</h1>
              <p className="text-xl text-gray-600 max-w-3xl mx-auto">
                더불어 사는 삶을 위해 설립된 따뜻한 사단법인입니다
              </p>
            </div>
            
            <Card className="border border-blue-200 shadow-xl bg-white hover-lift">
              <CardHeader>
                <div className="flex items-center space-x-3">
                  <div className="w-12 h-12 bg-gradient-to-br from-sky-400 to-sky-500 rounded-full flex items-center justify-center">
                    <Heart className="w-6 h-6 text-white" />
                  </div>
                  <CardTitle className="text-2xl text-blue-600">설립 취지</CardTitle>
                </div>
              </CardHeader>
              <CardContent className="space-y-6 text-lg leading-relaxed">
                <div className="p-6 bg-sky-100 rounded-xl border-l-4 border-sky-400">
                  <p className="text-blue-700">
                    이웃과 친척과 동료와 경쟁하는 삶이 아닌 더불어 사는 삶을 위하여 
                    희망연대노동조합 조합원과 지역주민들이 함께 설립한 법인입니다.
                  </p>
                </div>
                <div className="p-6 bg-gradient-to-r from-pink-50 to-rose-50 rounded-xl border-l-4 border-pink-400">
                  <p className="text-blue-700">
                    희망씨는 모든 아동청소년이 고유한 인격체로서 존중받고 어떠한 이유로도 
                    차별받지 않도록 아동권리실현에 앞장서는 활동을 진행합니다.
                  </p>
                </div>
                <div className="p-6 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-xl border-l-4 border-blue-400">
                  <p className="text-blue-700">
                    희망씨는 노동자가 자발적 주체가 되어 나눔연대·생활문화연대를 위한 
                    지속가능한 활동을 만들어 가는데 함께 합니다.
                  </p>
                </div>
                <div className="p-6 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border-l-4 border-green-400">
                  <p className="text-blue-700">
                    희망씨는 지역사회와 함께 아래로 향한 연대 일터와 삶터를 
                    바꾸기 위한 활동에 함께 합니다.
                  </p>
                </div>
              </CardContent>
            </Card>
          </div>
        );

      case "미션 및 비전":
        return (
          <div className="space-y-8">
            <div className="text-center">
              <h1 className="text-5xl text-blue-700 font-bold mb-4">미션 및 비전</h1>
              <p className="text-xl text-gray-600">희망씨가 추구하는 건강한 가치와 목표</p>
            </div>
            
            <div className="grid md:grid-cols-2 gap-8">
              <Card className="border border-blue-200 shadow-xl bg-white hover-lift">
                <CardHeader>
                  <div className="flex items-center space-x-3">
                    <Heart className="w-8 h-8 text-pink-500" />
                    <CardTitle className="text-2xl text-blue-600">미션</CardTitle>
                  </div>
                </CardHeader>
                <CardContent>
                  <ul className="space-y-4 text-lg">
                    <li className="flex items-center space-x-3 p-3 bg-sky-100 rounded-lg">
                      <div className="w-3 h-3 bg-sky-500 rounded-full flex-shrink-0"></div>
                      <span className="text-blue-700">아동청소년의 권리 실현</span>
                    </li>
                    <li className="flex items-center space-x-3 p-3 bg-sky-100 rounded-lg">
                      <div className="w-3 h-3 bg-sky-500 rounded-full flex-shrink-0"></div>
                      <span className="text-blue-700">노동자의 자발적 참여를 통한 연대 활동</span>
                    </li>
                    <li className="flex items-center space-x-3 p-3 bg-sky-100 rounded-lg">
                      <div className="w-3 h-3 bg-sky-500 rounded-full flex-shrink-0"></div>
                      <span className="text-blue-700">지역사회와의 상생 협력</span>
                    </li>
                    <li className="flex items-center space-x-3 p-3 bg-sky-100 rounded-lg">
                      <div className="w-3 h-3 bg-sky-500 rounded-full flex-shrink-0"></div>
                      <span className="text-blue-700">사회적 약자를 위한 지원 활동</span>
                    </li>
                  </ul>
                </CardContent>
              </Card>

              <Card className="border border-blue-200 shadow-xl bg-white hover-lift">
                <CardHeader>
                  <div className="flex items-center space-x-3">
                    <Globe className="w-8 h-8 text-sky-500" />
                    <CardTitle className="text-2xl text-blue-600">비전</CardTitle>
                  </div>
                </CardHeader>
                <CardContent>
                  <ul className="space-y-4 text-lg">
                    <li className="flex items-center space-x-3 p-3 bg-sky-100 rounded-lg">
                      <div className="w-3 h-3 bg-blue-500 rounded-full flex-shrink-0"></div>
                      <span className="text-blue-700">모든 아동이 차별받지 않는 사회</span>
                    </li>
                    <li className="flex items-center space-x-3 p-3 bg-sky-100 rounded-lg">
                      <div className="w-3 h-3 bg-blue-500 rounded-full flex-shrink-0"></div>
                      <span className="text-blue-700">경쟁이 아닌 상생의 공동체</span>
                    </li>
                    <li className="flex items-center space-x-3 p-3 bg-sky-100 rounded-lg">
                      <div className="w-3 h-3 bg-blue-500 rounded-full flex-shrink-0"></div>
                      <span className="text-blue-700">지속가능한 나눔과 연대</span>
                    </li>
                    <li className="flex items-center space-x-3 p-3 bg-sky-100 rounded-lg">
                      <div className="w-3 h-3 bg-blue-500 rounded-full flex-shrink-0"></div>
                      <span className="text-blue-700">더불어 사는 건강한 사회</span>
                    </li>
                  </ul>
                </CardContent>
              </Card>
            </div>
          </div>
        );

      case "오시는길":
        return (
          <div className="space-y-8">
            <div className="text-center">
              <h1 className="text-5xl text-blue-700 font-bold mb-4">오시는길</h1>
              <p className="text-xl text-gray-600">사단법인 희망씨 사무실 위치 안내</p>
            </div>
            
            <div className="grid md:grid-cols-2 gap-8">
              <Card className="border border-blue-200 shadow-xl bg-white">
                <CardHeader>
                  <CardTitle className="flex items-center space-x-2">
                    <MapPin className="w-5 h-5 text-blue-500" />
                    <span className="text-blue-600">주소</span>
                  </CardTitle>
                </CardHeader>
                <CardContent className="space-y-4">
                  <p className="text-lg text-blue-700 font-medium">서울특별시 중구 을지로 100</p>
                  <p className="text-gray-600">희망빌딩 3층</p>
                  
                  <Separator />
                  
                  <div className="space-y-2">
                    <div className="flex items-center space-x-2">
                      <Phone className="w-4 h-4 text-blue-500" />
                      <span className="text-blue-700">전화: 02-1234-5678</span>
                    </div>
                    <div className="flex items-center space-x-2">
                      <Mail className="w-4 h-4 text-blue-500" />
                      <span className="text-blue-700">이메일: info@hopeseed.org</span>
                    </div>
                  </div>
                </CardContent>
              </Card>

              <Card className="border border-blue-200 shadow-xl bg-white">
                <CardHeader>
                  <CardTitle className="text-blue-600">대중교통 이용안내</CardTitle>
                </CardHeader>
                <CardContent className="space-y-4">
                  <div className="p-4 bg-sky-100 rounded-lg">
                    <h4 className="font-medium text-blue-600 mb-2">지하철</h4>
                    <p className="text-sm text-blue-700">
                      2호선 을지로입구역 3번 출구 도보 5분<br/>
                      1호선 종각역 4번 출구 도보 10분
                    </p>
                  </div>
                  <div className="p-4 bg-sky-100 rounded-lg">
                    <h4 className="font-medium text-blue-600 mb-2">버스</h4>
                    <p className="text-sm text-blue-700">
                      간선버스: 123, 456, 789<br/>
                      지선버스: 1001, 1002
                    </p>
                  </div>
                </CardContent>
              </Card>
            </div>
          </div>
        );

      case "정기후원(cms)":
        return (
          <div className="space-y-8">
            <div className="text-center">
              <h1 className="text-5xl text-blue-700 font-bold mb-4">정기후원</h1>
              <p className="text-xl text-gray-600">매월 일정 금액으로 지속적인 나눔에 참여해보세요</p>
            </div>
            
            <Card className="max-w-4xl mx-auto border border-blue-200 shadow-2xl bg-white hover-lift">
              <CardHeader className="text-center pb-2">
                <div className="w-20 h-20 bg-gradient-to-br from-sky-400 to-sky-500 rounded-full flex items-center justify-center mx-auto mb-4">
                  <Heart className="w-10 h-10 text-white" />
                </div>
                <CardTitle className="text-3xl text-blue-600">정기후원 안내</CardTitle>
                <CardDescription className="text-lg text-gray-600 mt-2">
                  CMS 자동이체를 통한 편리하고 안전한 후원
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-8 p-8">
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                  {[
                    { amount: "월 1만원", title: "아이돌보미", gradient: "from-pink-400 to-rose-400", emoji: "💝" },
                    { amount: "월 2만원", title: "희망지킴이", gradient: "from-sky-400 to-sky-500", emoji: "⭐" },
                    { amount: "월 3만원", title: "나눔파트너", gradient: "from-blue-400 to-cyan-400", emoji: "🤝" },
                    { amount: "월 5만원", title: "희망후원자", gradient: "from-blue-500 to-blue-600", emoji: "💙" }
                  ].map((option, index) => (
                    <div key={index} className="text-center p-6 bg-sky-100 rounded-xl border-2 border-blue-200 hover:border-blue-400 cursor-pointer hover-lift transition-all duration-300 group">
                      <div className={`w-12 h-12 bg-gradient-to-br ${option.gradient} rounded-full flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform duration-300`}>
                        <span className="text-2xl">{option.emoji}</span>
                      </div>
                      <p className="font-medium text-lg text-blue-700">{option.amount}</p>
                      <p className="text-sm text-gray-600 mt-1">{option.title}</p>
                    </div>
                  ))}
                </div>
                
                <div className="bg-gradient-to-r from-sky-100 to-blue-100 p-6 rounded-xl border-l-4 border-sky-400">
                  <h4 className="font-medium text-blue-700 mb-3 text-lg flex items-center">
                    <Star className="w-5 h-5 mr-2 text-sky-500" />
                    정기후원 혜택
                  </h4>
                  <ul className="text-blue-600 space-y-2">
                    <li className="flex items-center space-x-2">
                      <div className="w-2 h-2 bg-sky-500 rounded-full"></div>
                      <span>연말정산 기부금 공제 혜택</span>
                    </li>
                    <li className="flex items-center space-x-2">
                      <div className="w-2 h-2 bg-sky-500 rounded-full"></div>
                      <span>정기 활동 소식지 발송</span>
                    </li>
                    <li className="flex items-center space-x-2">
                      <div className="w-2 h-2 bg-sky-500 rounded-full"></div>
                      <span>희망씨 행사 초대</span>
                    </li>
                    <li className="flex items-center space-x-2">
                      <div className="w-2 h-2 bg-sky-500 rounded-full"></div>
                      <span>감사패 및 감사장 증정</span>
                    </li>
                  </ul>
                </div>
                
                <Button className="w-full bg-gradient-to-r from-sky-500 to-blue-500 hover:from-sky-600 hover:to-blue-600 text-white hover:scale-105 transition-all duration-300 shadow-xl text-lg py-6 rounded-xl" size="lg">
                  정기후원 신청하기
                </Button>
              </CardContent>
            </Card>
          </div>
        );

      case "공지사항":
        return (
          <div className="space-y-8">
            <div className="text-center">
              <h1 className="text-5xl text-blue-700 font-bold mb-4">공지사항</h1>
              <p className="text-xl text-gray-600">희망씨의 최신 소식과 공지를 확인하세요</p>
            </div>
            
            <div className="space-y-4">
              {[
                { title: "2024년 정기총회 개최 안내", date: "2024-02-15", important: true, emoji: "🏛️" },
                { title: "겨울방학 아동지원 프로그램 참가자 모집", date: "2024-01-20", important: false, emoji: "❄️" },
                { title: "후원금 사용내역 공개", date: "2024-01-10", important: false, emoji: "💰" },
                { title: "네팔 나눔연대여행 참가자 모집", date: "2023-12-28", important: false, emoji: "🏔️" },
                { title: "연말연시 사무실 운영 안내", date: "2023-12-20", important: false, emoji: "🎊" }
              ].map((notice, index) => (
                <Card key={index} className="hover-lift cursor-pointer border border-blue-200 shadow-md bg-white overflow-hidden group">
                  <CardContent className="p-6">
                    <div className="flex justify-between items-start">
                      <div className="flex items-start space-x-4">
                        <div className="text-2xl group-hover:scale-110 transition-transform duration-300">
                          {notice.emoji}
                        </div>
                        <div className="flex-1">
                          <div className="flex items-center space-x-3 mb-2">
                            {notice.important && (
                              <span className="bg-gradient-to-r from-red-400 to-pink-400 text-white text-xs px-3 py-1 rounded-full">
                                중요
                              </span>
                            )}
                            <h3 className="font-medium text-lg text-blue-700 group-hover:text-sky-500 transition-colors">{notice.title}</h3>
                          </div>
                          <p className="text-gray-500 text-sm flex items-center">
                            <Calendar className="w-4 h-4 mr-1" />
                            {notice.date}
                          </p>
                        </div>
                      </div>
                      <div className="text-gray-300 group-hover:text-sky-500 transition-colors">
                        <ArrowLeft className="w-5 h-5 rotate-180" />
                      </div>
                    </div>
                  </CardContent>
                </Card>
              ))}
            </div>
          </div>
        );

      default:
        return (
          <div className="text-center py-16">
            <div className="w-32 h-32 bg-gradient-to-br from-sky-400 to-sky-500 rounded-full flex items-center justify-center mx-auto mb-6">
              <Building className="w-16 h-16 text-white" />
            </div>
            <h1 className="text-4xl text-blue-700 font-bold mb-4">{page}</h1>
            <p className="text-xl text-gray-600 mb-8">
              해당 페이지는 곧 만나실 수 있습니다!
            </p>
            <Button 
              className="bg-gradient-to-r from-sky-500 to-blue-500 hover:from-sky-600 hover:to-blue-600 text-white hover:scale-105 transition-all duration-300 shadow-xl rounded-lg px-8 py-3"
              onClick={() => onPageChange("home")}
            >
              홈으로 돌아가기
            </Button>
          </div>
        );
    }
  };

  return (
    <div className="min-h-screen bg-sky-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="mb-6">
          <Button
            variant="ghost"
            onClick={() => onPageChange("home")}
            className="text-blue-600 hover:text-sky-500 hover:bg-sky-100 rounded-lg px-6 py-3 transition-all duration-300"
          >
            <ArrowLeft className="w-4 h-4 mr-2" />
            홈으로 돌아가기
          </Button>
        </div>
        
        {renderContent()}
      </div>
    </div>
  );
}