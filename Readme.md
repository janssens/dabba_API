# UML Model

[Zone|name]1-*>[Restaurant|name;lat;lon;pic;tripadvisor_id]
[Zone]1-admin*>[User|firstname;lastname;email;password;amount]
[Stock|type;owner]
[Stock]1-0..1>[User]
[Stock]1-0..1>[Restaurant]
[Stock]1-0..1>[Zone]
[Container|name;price]<-*[Movement]
[Movement|created_at;reason]1-from>[Stock]
[Movement|created_at;reason]1-to>[Stock]
[Cms|title;from;to;content;pic;class;type;button_label;url]
[Promo|from_date;to_date;qty]1->[Container]
[PaymentMethod|token]<*-[User]
[Order|amount;status]<*-[User]
[Cart]1-1>[User]
[Cart]1-*>[Item]
[Item|qty]->[Container]
[Code|token]*-1>[Container]

ENUM Movement reason
- RETURN (USER -> RESTAURANT)
- BUY (RESTAURANT -> USER) 
- INVENTORY (ANY -> 0 or 0 -> ANY)
- LOGISTIC (FRANCHISE -> RESTAURANT or RESTAURANT -> FRANCHISE)

https://yuml.me/diagram/scruffy/class/draw