package newAll;

import java.io.BufferedInputStream;
import java.io.BufferedWriter;
import java.io.File;
import java.io.*;
import java.io.FileInputStream;
import java.io.FileReader;
import java.io.FileWriter;
import java.io.IOException;
import javax.swing.*;

import java.io.InputStream;
import java.io.OutputStream;
import java.io.Reader;
import java.io.InputStreamReader;
import java.nio.charset.StandardCharsets;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.Map;

import javax.xml.parsers.DocumentBuilderFactory;
import javax.xml.parsers.ParserConfigurationException;
import javax.xml.transform.OutputKeys;
import javax.xml.transform.Transformer;
import javax.xml.transform.TransformerConfigurationException;
import javax.xml.transform.TransformerException;
import javax.xml.transform.TransformerFactory;
import javax.xml.transform.TransformerFactoryConfigurationError;
import javax.xml.transform.dom.DOMSource;
import javax.xml.transform.stream.StreamResult;

import org.w3c.dom.CDATASection;
import org.w3c.dom.Document;
import org.w3c.dom.Element;

import javax.xml.parsers.DocumentBuilder;
import org.w3c.dom.Node;
import org.w3c.dom.NodeList;
import org.xml.sax.SAXException;



public class GetFilePDF {
	
	public static int PdfCleanUpContentOperatorOnlyText = 0; // 1 удаляет все содержимое области!
	public static int PdfContentStreamProcessorParse = 0; // если файл парсится, создаем xml
	public static int TJnum = 0; 
	public static ArrayList Tj_blocks_nums_on_post = new ArrayList();
	public static Map<Integer, String> colorSpaceNumberObjectsMAP = new HashMap<Integer, String>();
	public static Map<String, String> colorSpaceAndStringMAP = new HashMap<String, String>();
	public static Map<String, String[]> colorSpaceMAP = new HashMap<String, String[]>();
	
	public static String directoryPath = "";
	public static String directoryPdfEdit = "";
	public static String fileNamePdf = "";
	public static String folderNameFile = "";
	public static String directoryPathWork = "";
	public static String textFolderPath = "";
	public static String imgFolderPath = "";
	
	public static String notFoundGlyph = ""; 
	public static String fontFile = ""; 
	
	public static String currentdir = "";
	
	public static String folderId = "";
	
	public static int pageNumber = 0;
	
	
	public static Document docXml = null;
	public static Element root = null;
	public static Element TJ = null;


	public GetFilePDF()
	{
			GetFilePDF.directoryPathWork = GetFilePDF.currentdir + "/work/";
			GetFilePDF.directoryPathWork = GetFilePDF.currentdir + "/work/" + GetFilePDF.folderId + "/projects/";
			GetFilePDF.directoryPathWork = GetFilePDF.currentdir + "/work/" + GetFilePDF.folderId + "/";
			GetFilePDF.directoryPath = GetFilePDF.currentdir + "/work/" + GetFilePDF.folderId + "/";
			GetFilePDF.directoryPdfEdit = GetFilePDF.currentdir + "/";

			GetFilePDF.textFolderPath = GetFilePDF.directoryPathWork + GetFilePDF.folderNameFile + "/text";
			GetFilePDF.imgFolderPath = GetFilePDF.directoryPathWork + GetFilePDF.folderNameFile + "/img";
	}
	
	public static String GetFilePath(String currentdir)
	{
		String filePath = "";
		try{
			filePath = GetFilePDF.readFile(currentdir + "/path.txt"); 
		} catch (Exception ExceptionFilePath) { 
			System.out.println(ExceptionFilePath); 
			System.out.println(filePath); 
		}
		return filePath;
	}
	
	public static String GetFileName(String currentdir)
	{
		String filePDF = "";
		try{
			filePDF = GetFilePDF.readFileUTF(currentdir + "/file.txt");
		} catch (Exception ExceptionFileName) { 
			System.out.println(ExceptionFileName); 
		}	
		return filePDF;
	}	
	

	
	public static String readFile(String filename)
	{
	    String content = null;
	    File file = new File(filename); //for ex foo.txt
	    FileReader reader = null;
	    try {
	        reader = new FileReader(file);
	        char[] chars = new char[(int) file.length()];
	        reader.read(chars);
	        content = new String(chars);
	        reader.close();
	    } catch (IOException e) {
	        e.printStackTrace();
	    } finally {
	        if(reader !=null){
			reader.close();
			}
	    }
	    return content;
	}
	
	public static String readFileUTF(String filename)
	{	
	    File file = new File(filename);
	    String fileContents;
	    try (InputStream fileStream = new FileInputStream(file);
	         InputStream bufStream = new BufferedInputStream(fileStream);
	         Reader reader = new InputStreamReader(bufStream, StandardCharsets.UTF_8)) {
	        StringBuilder fileContentsBuilder = new StringBuilder();
	        char[] buffer = new char[1024];
	        int charsRead;
	        while ((charsRead = reader.read(buffer)) != -1) {
	            fileContentsBuilder.append(buffer, 0, charsRead);
	        }
	        fileContents = fileContentsBuilder.toString();
	    } catch (IOException e) {
	        throw new RuntimeException(e.getMessage(), e);
	    }
	    //System.out.println(fileContents);	
	    return fileContents;
	} 
	
	
	public static void remouveFiles(String directoryPath)
	{
		File myPath = new File(directoryPath); 
		if(myPath.isDirectory()){
			for (File myFile : myPath.listFiles()) if (myFile.isFile()) myFile.delete(); 
		} else {myPath.mkdirs();}
	}	
	
	public static void runProcess(String className)
	{
		GetFilePDF.writeTxtFile(GetFilePDF.directoryPdfEdit + "scriptTime/", className + ".txt", "1");
	}
	
	public static void stopProcess(String className)
	{
		File file = new File(GetFilePDF.directoryPdfEdit + "scriptTime/" + className + ".txt"); 
		file.delete();
		if(GetFilePDF.notFoundGlyph != ""){
			GetFilePDF.writeTxtFile(GetFilePDF.directoryPathWork + GetFilePDF.folderNameFile, "/error.txt", GetFilePDF.notFoundGlyph);
		} else {
			err.delete(); // Файл ошибки удаляется всеми классами, поэтому этот на всякий случай!
		}
	}	
	
	public static void writeTxtFile(String path, String fileName, String message){
		try{
			FileWriter fw = new FileWriter(path + fileName); // textFolder
	        BufferedWriter bw = new BufferedWriter(fw);
	        bw.write(message);
	        bw.close();
        }
		catch(IOException ex){}		
	}
	
    // запись в файл используя OutputStream
	public static void writeTxtOutput(byte[] st, String path) {
    	try{
	    	OutputStream outputStream = new FileOutputStream(path);
	        // передаем полученную строку st и приводим её к byte массиву.
	    	outputStream.write(st);
	        outputStream.close();
        } catch(IOException ex){}		        
    }	
	
	
	public static String CurrentDir(){
		String path=System.getProperty("java.class.path");
		String FileSeparator=(String)System.getProperty("file.separator");
		return path.substring(0, path.lastIndexOf(FileSeparator)+1);
	 }	
	

	public static void CreateXMLstart(){
		DocumentBuilderFactory factory = DocumentBuilderFactory.newInstance();
		factory.setNamespaceAware(true);
		try {
			GetFilePDF.docXml = factory.newDocumentBuilder().newDocument();
			GetFilePDF.root = GetFilePDF.docXml.createElement("root");
			GetFilePDF.root.setAttribute("xmlns", "http://www.javacore.ru/schemas/");
			GetFilePDF.root.setAttribute("version", "1.0");
			GetFilePDF.docXml.appendChild(GetFilePDF.root);	
		} catch (ParserConfigurationException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}			
	}

	public static void CreateXMLoadField(String name, String text){
		Element item = GetFilePDF.docXml.createElement(name);
		item.setTextContent(text);
		GetFilePDF.root.appendChild(item);		
	}
	
	public static void CreateXMLoadFieldTj(String Tjid){
		GetFilePDF.TJ = GetFilePDF.docXml.createElement("TJ");
		GetFilePDF.TJ.setAttribute("id", Tjid);
		GetFilePDF.root.appendChild(GetFilePDF.TJ);		
	}

	public static void CreateXMLoadFieldChildTj( String name, String text, int id ){  
		
		Element item = GetFilePDF.docXml.createElement(name);
		
		if( name.equalsIgnoreCase("text") ){
		    char[] symbs = text.toCharArray();
		    String textNums = "";
		    for(int i = 0; i < symbs.length; i++){
		    	int nn = text.codePointAt(i);
		    	if(i>0){ textNums += ";";}
		    	textNums += nn;
		    }
		    item.setTextContent(textNums);
		} else{
			item.setTextContent(text);
		}
		GetFilePDF.root.getElementsByTagName("TJ").item(id).appendChild(item);
	}
	
	public static void CreateXMLfinish( String XML_file_path){

		File fileXML = new File(XML_file_path);
		 
		Transformer transformer;
		try {
			transformer = TransformerFactory.newInstance().newTransformer();
			transformer.setOutputProperty(OutputKeys.INDENT, "yes");
			try {
				transformer.transform(new DOMSource(GetFilePDF.docXml), new StreamResult(fileXML));
			} catch (TransformerException e) {
				// TODO Auto-generated catch block
				e.printStackTrace();
			}				
		} catch (TransformerConfigurationException | TransformerFactoryConfigurationError e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		GetFilePDF.docXml = null;
		GetFilePDF.root = null;
		GetFilePDF.TJ = null;	
		GetFilePDF.TJnum = 0;
	}	

	
}


