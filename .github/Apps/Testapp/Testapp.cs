// Author: daveijsclub
// Minimal Windows Forms app: Date, Time, Computer Name, File > Exit menu

using System;
using System.Windows.Forms;

class MainForm : Form
{
    Label infoLabel;
    Timer timer;

    public MainForm()
    {
        Text = "System Info";
        Width = 350;
        Height = 180;

        // Menu
        var menu = new MenuStrip();
        var fileMenu = new ToolStripMenuItem("File");
        var exitItem = new ToolStripMenuItem("Exit", null, (s, e) => Close());
        fileMenu.DropDownItems.Add(exitItem);
        menu.Items.Add(fileMenu);
        MainMenuStrip = menu;
        Controls.Add(menu);

        // Info label
        infoLabel = new Label
        {
            Dock = DockStyle.Fill,
            Font = new System.Drawing.Font("Segoe UI", 12),
            TextAlign = System.Drawing.ContentAlignment.MiddleCenter
        };
        Controls.Add(infoLabel);

        // Timer for updating time
        timer = new Timer { Interval = 1000 };
        timer.Tick += (s, e) => UpdateInfo();
        timer.Start();

        UpdateInfo();
    }

    void UpdateInfo()
    {
        infoLabel.Text = $"Date: {DateTime.Now:yyyy-MM-dd}\n" +
                         $"Time: {DateTime.Now:HH:mm:ss}\n" +
                         $"Computer Name: {Environment.MachineName}";
    }
}

static class Program
{
    [STAThread]
    static void Main()
    {
        Application.EnableVisualStyles();
        Application.SetCompatibleTextRenderingDefault(false);
        Application.Run(new MainForm());
    }
}
