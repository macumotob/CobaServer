using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Net.Mail;


namespace Maxbuk.Server.Core
{
  public class CobaMail
  {
    public static void SendMail(string fromAddress, string toAddress)
    {
      SmtpClient client = new SmtpClient();
      client.Port = 587;
      client.Host = "smtp.gmail.com";
      client.EnableSsl = true;
      client.Timeout = 10000;
      client.DeliveryMethod = SmtpDeliveryMethod.Network;
      client.UseDefaultCredentials = false;
      client.Credentials = new System.Net.NetworkCredential("user@gmail.com", "password");

      MailMessage mm = new MailMessage(fromAddress, toAddress, "test", "test");
      mm.BodyEncoding = UTF8Encoding.UTF8;
      mm.DeliveryNotificationOptions = DeliveryNotificationOptions.OnFailure;

      client.Send(mm);
    }
  }
}
