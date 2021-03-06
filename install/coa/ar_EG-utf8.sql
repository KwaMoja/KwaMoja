
INSERT INTO `accountsection` VALUES (10,'ar_EG.utf8','الأصول');
INSERT INTO `accountsection` VALUES (20,'ar_EG.utf8','التزامات');
INSERT INTO `accountsection` VALUES (30,'ar_EG.utf8','دخل');
INSERT INTO `accountsection` VALUES (40,'ar_EG.utf8','التكاليف');

INSERT INTO `accountgroups` VALUES ('الاصول الثابتة','10','ar_EG.utf8',10,0,3000,'','');
INSERT INTO `accountgroups` VALUES ('راد استشارات','100','ar_EG.utf8',30,1,8000,'','');
INSERT INTO `accountgroups` VALUES ('مصروفات ادارية و عمومية','110','ar_EG.utf8',40,1,12000,'','');
INSERT INTO `accountgroups` VALUES ('مصروفات الاجور','120','ar_EG.utf8',40,1,11000,'','');
INSERT INTO `accountgroups` VALUES ('الاصول المتداولة','20','ar_EG.utf8',10,0,1000,'','');
INSERT INTO `accountgroups` VALUES ('التزامات قصيرة الاجل','30','ar_EG.utf8',20,0,4000,'','');
INSERT INTO `accountgroups` VALUES ('التمات طويلة الاجل','40','ar_EG.utf8',20,0,5000,'','');
INSERT INTO `accountgroups` VALUES ('المخزون كاصل','50','ar_EG.utf8',10,0,2000,'','');
INSERT INTO `accountgroups` VALUES ('ايراد المبيعات','60','ar_EG.utf8',30,1,7000,'','');
INSERT INTO `accountgroups` VALUES ('ايردات اخرى','70','ar_EG.utf8',30,1,9000,'','');
INSERT INTO `accountgroups` VALUES ('تكلفة البضاعة المباعة','80','ar_EG.utf8',40,1,10000,'','');
INSERT INTO `accountgroups` VALUES ('حقوق الملكية','90','ar_EG.utf8',20,0,6000,'','');

INSERT INTO `chartmaster` VALUES ('1060','ar_EG.utf8','نقدية بالبنك','الاصول المتداولة',-1,'20');
INSERT INTO `chartmaster` VALUES ('1065','ar_EG.utf8','نقديةبالصندوق','الاصول المتداولة',-1,'20');
INSERT INTO `chartmaster` VALUES ('1200','ar_EG.utf8','عملاء','الاصول المتداولة',-1,'20');
INSERT INTO `chartmaster` VALUES ('1205','ar_EG.utf8','مخصص ديون معدومة','الاصول المتداولة',-1,'20');
INSERT INTO `chartmaster` VALUES ('1520','ar_EG.utf8','مخزون - قطع غيار كمبيوتر','المخزون كاصل',-1,'50');
INSERT INTO `chartmaster` VALUES ('1530','ar_EG.utf8','مخزون - برامج','المخزون كاصل',-1,'50');
INSERT INTO `chartmaster` VALUES ('1540','ar_EG.utf8','مخزون - قطع اخرى','المخزون كاصل',-1,'50');
INSERT INTO `chartmaster` VALUES ('1820','ar_EG.utf8','اثاث مكتبى و معدات','الاصول الثابتة',-1,'10');
INSERT INTO `chartmaster` VALUES ('1825','ar_EG.utf8','مخصص اهلاك اثاث مكتبى و معدات','الاصول الثابتة',-1,'10');
INSERT INTO `chartmaster` VALUES ('1840','ar_EG.utf8','سيارات','الاصول الثابتة',-1,'10');
INSERT INTO `chartmaster` VALUES ('1845','ar_EG.utf8','مخصص اهلاك سيارات','الاصول الثابتة',-1,'10');
INSERT INTO `chartmaster` VALUES ('2100','ar_EG.utf8','موردين','التزامات قصيرة الاجل',-1,'30');
INSERT INTO `chartmaster` VALUES ('2160','ar_EG.utf8','ضريبة شركات مستحقة','التزامات قصيرة الاجل',-1,'30');
INSERT INTO `chartmaster` VALUES ('2190','ar_EG.utf8','ضريبة دخل مستحقة','التزامات قصيرة الاجل',-1,'30');
INSERT INTO `chartmaster` VALUES ('2210','ar_EG.utf8','عمال شركات الدائنة','التزامات قصيرة الاجل',-1,'30');
INSERT INTO `chartmaster` VALUES ('2220','ar_EG.utf8','عطلة الأجر المستحق','التزامات قصيرة الاجل',-1,'30');
INSERT INTO `chartmaster` VALUES ('2250','ar_EG.utf8','خطة المعاشات التقاعدية المستحقة','التزامات قصيرة الاجل',-1,'30');
INSERT INTO `chartmaster` VALUES ('2260','ar_EG.utf8','تأمين فرص العمل الدائنة','التزامات قصيرة الاجل',-1,'30');
INSERT INTO `chartmaster` VALUES ('2280','ar_EG.utf8','ضرائب مرتبات مستحقة','التزامات قصيرة الاجل',-1,'30');
INSERT INTO `chartmaster` VALUES ('2310','ar_EG.utf8','ضريبة مبيعات (10%)','التزامات قصيرة الاجل',-1,'30');
INSERT INTO `chartmaster` VALUES ('2320','ar_EG.utf8','ضريبة مبيعات (14%)','التزامات قصيرة الاجل',-1,'30');
INSERT INTO `chartmaster` VALUES ('2330','ar_EG.utf8','ضريبة مبيعات (30%)','التزامات قصيرة الاجل',-1,'30');
INSERT INTO `chartmaster` VALUES ('2620','ar_EG.utf8','قروض من البنوك','التمات طويلة الاجل',-1,'40');
INSERT INTO `chartmaster` VALUES ('2680','ar_EG.utf8','قروض من حملة الاسهم','التمات طويلة الاجل',-1,'40');
INSERT INTO `chartmaster` VALUES ('3350','ar_EG.utf8','الاسهم','حقوق الملكية',-1,'90');
INSERT INTO `chartmaster` VALUES ('4020','ar_EG.utf8','مبيعات - قطع غيار','ايراد المبيعات',-1,'60');
INSERT INTO `chartmaster` VALUES ('4030','ar_EG.utf8','مبيعات برامج','ايراد المبيعات',-1,'60');
INSERT INTO `chartmaster` VALUES ('4040','ar_EG.utf8','مبيعات اخرى','ايراد المبيعات',-1,'60');
INSERT INTO `chartmaster` VALUES ('4320','ar_EG.utf8','استشارات','راد استشارات',-1,'100');
INSERT INTO `chartmaster` VALUES ('4330','ar_EG.utf8','برمجة','راد استشارات',-1,'100');
INSERT INTO `chartmaster` VALUES ('4430','ar_EG.utf8','شحن و تعبئة','ايردات اخرى',-1,'70');
INSERT INTO `chartmaster` VALUES ('4440','ar_EG.utf8','فائدة','ايردات اخرى',-1,'70');
INSERT INTO `chartmaster` VALUES ('4450','ar_EG.utf8','ارباح تغيير عملة','ايردات اخرى',-1,'70');
INSERT INTO `chartmaster` VALUES ('5010','ar_EG.utf8','مشتريات','تكلفة البضاعة المباعة',-1,'80');
INSERT INTO `chartmaster` VALUES ('5020','ar_EG.utf8','تكلفة البضاعة المباعة - قطع غيار','تكلفة البضاعة المباعة',-1,'80');
INSERT INTO `chartmaster` VALUES ('5030','ar_EG.utf8','تكلفة البضاعة المباعة - برامج','تكلفة البضاعة المباعة',-1,'80');
INSERT INTO `chartmaster` VALUES ('5040','ar_EG.utf8','تكلفة البضاعة المباعة - اخرى','تكلفة البضاعة المباعة',-1,'80');
INSERT INTO `chartmaster` VALUES ('5100','ar_EG.utf8','شحن','تكلفة البضاعة المباعة',-1,'80');
INSERT INTO `chartmaster` VALUES ('5410','ar_EG.utf8','المرتبات','مصروفات الاجور',-1,'120');
INSERT INTO `chartmaster` VALUES ('5420','ar_EG.utf8','نفقات التأمين على البطالة','مصروفات الاجور',-1,'120');
INSERT INTO `chartmaster` VALUES ('5430','ar_EG.utf8','نفقات خطة المعاشات التقاعدية','مصروفات الاجور',-1,'120');
INSERT INTO `chartmaster` VALUES ('5440','ar_EG.utf8','نفقة عمال شركات','مصروفات الاجور',-1,'120');
INSERT INTO `chartmaster` VALUES ('5470','ar_EG.utf8','استحقاقات الموظفين','مصروفات الاجور',-1,'120');
INSERT INTO `chartmaster` VALUES ('5610','ar_EG.utf8','قانونية و محاسبية','مصروفات ادارية و عمومية',-1,'110');
INSERT INTO `chartmaster` VALUES ('5615','ar_EG.utf8','دعاية و اعلان','مصروفات ادارية و عمومية',-1,'110');
INSERT INTO `chartmaster` VALUES ('5620','ar_EG.utf8','ديون معدومة','مصروفات ادارية و عمومية',-1,'110');
INSERT INTO `chartmaster` VALUES ('5650','ar_EG.utf8','تكلفة رأس المال بدل نفقات','مصروفات ادارية و عمومية',-1,'110');
INSERT INTO `chartmaster` VALUES ('5660','ar_EG.utf8','مصاريف اهلاك','مصروفات ادارية و عمومية',-1,'110');
INSERT INTO `chartmaster` VALUES ('5680','ar_EG.utf8','ضريبة دخل','مصروفات ادارية و عمومية',-1,'110');
INSERT INTO `chartmaster` VALUES ('5685','ar_EG.utf8','تامين','مصروفات ادارية و عمومية',-1,'110');
INSERT INTO `chartmaster` VALUES ('5690','ar_EG.utf8','قوائد و مصاريف بنكية','مصروفات ادارية و عمومية',-1,'110');
INSERT INTO `chartmaster` VALUES ('5700','ar_EG.utf8','مهمات مكتبية','مصروفات ادارية و عمومية',-1,'110');
INSERT INTO `chartmaster` VALUES ('5760','ar_EG.utf8','ايجار','مصروفات ادارية و عمومية',-1,'110');
INSERT INTO `chartmaster` VALUES ('5765','ar_EG.utf8','اصلاح و صيانة','مصروفات ادارية و عمومية',-1,'110');
INSERT INTO `chartmaster` VALUES ('5780','ar_EG.utf8','تلفون','مصروفات ادارية و عمومية',-1,'110');
INSERT INTO `chartmaster` VALUES ('5785','ar_EG.utf8','مصاريف سفر','مصروفات ادارية و عمومية',-1,'110');
INSERT INTO `chartmaster` VALUES ('5790','ar_EG.utf8','مرافق','مصروفات ادارية و عمومية',-1,'110');
INSERT INTO `chartmaster` VALUES ('5795','ar_EG.utf8','رسوم','مصروفات ادارية و عمومية',-1,'110');
INSERT INTO `chartmaster` VALUES ('5800','ar_EG.utf8','رخص','مصروفات ادارية و عمومية',-1,'110');
INSERT INTO `chartmaster` VALUES ('5810','ar_EG.utf8','خسارة تحويل عملة','مصروفات ادارية و عمومية',-1,'110');
